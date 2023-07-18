<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Conversion;
use App\Models\CustomField;
use App\Models\Faq;
use App\Mail\SendTicket;
use App\Mail\SendTicketAdmin;
use App\Mail\SendTicketReply;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Knowledge;
use App\Models\Utility;
use App\Models\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;

class HomeController extends Controller
{
    private  $language;
    public function __construct()
    {
        if (!file_exists(storage_path() . "/installed")) {
            return redirect('install');
        }

        $language = Utility::getSettingValByName('DEFAULT_LANG');
        \App::setLocale(isset($language) ? $language : 'en');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if (!file_exists(storage_path() . "/installed")) {
            return redirect('install');
        }
        if (Auth::user()) {
            // dd(Auth::user());
            return redirect()->route('admin.dashboard');
        }

        $customFields = CustomField::orderBy('order')->get();
        $categories   = Category::get();
        $setting      = Utility::settings();

        return view('home', compact('categories', 'customFields', 'setting'));
    }

    public function search()
    {
        $setting      = Utility::settings();
        return view('search', compact('setting'));
    }

    public function faq()
    {
        $setting      = Utility::settings();
        if ($setting['FAQ'] == 'on') {
            $faqs = Faq::get();

            return view('faq', compact('faqs', 'setting'));
        } else {
            return redirect('/');
        }
    }

    public function ticketSearch(Request $request)
    {
        $validation = [
            'ticket_id' => ['required'],
            'email' => ['required'],
        ];

        $this->validate($request, $validation);
        $ticket = Ticket::where('ticket_id', '=', $request->ticket_id)->where('email', '=', $request->email)->first();

        if ($ticket) {
            return redirect()->route('home.view', Crypt::encrypt($ticket->ticket_id));
        } else {
            return redirect()->back()->with('info', __('Invalid Ticket Number'));
        }

        return view('search');
    }

    public function store(Request $request)
    {
        $user = \Auth::user();
        $validation = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'category' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'status' => 'required|string|max:100',
            'description' => 'required',
        ];

        if (env('RECAPTCHA_MODULE') == 'yes') {
            $validation['g-recaptcha-response'] = 'required|captcha';
        }

        $this->validate($request, $validation);

        $post              = $request->all();
        $post['ticket_id'] = time();
        $data              = [];
        if ($request->hasfile('attachments')) {
            $errors = [];
            foreach ($request->file('attachments') as $filekey => $file) {
                $name = $file->getClientOriginalName();
                $dir        = ('tickets/' . $post['ticket_id']);
                $path = Utility::multipalFileUpload($request, 'attachments', $name, $dir, $filekey, []);

                if ($path['flag'] == 1) {
                    $data[] = $path['url'];
                } elseif ($path['flag'] == 0) {
                    $errors = __($path['msg']);
                }
                // else{
                // return redirect()->route('tickets.store', \Auth::user()->id)->with('error', __($path['msg']));
                // }
            }
        }
        $post['attachments'] = json_encode($data);
        $ticket              = Ticket::create($post);
        CustomField::saveData($ticket, $request->customField);


        // Send Email to User

        $uArr = [
            // 'email' => $user->email,
            // 'password' => $request->password,

            'name' => $request->name,
            'email' => $request->email,
            'category' => $request->category,
            'subject' => $request->subject,
            'status' => $request->status,
            'description' => $request->description,
        ];

        // $resp = Utility::sendEmailTemplate('create_ticket', [$ticket->email], $uArr);

        // Send Email to User
        try {
            Mail::to($ticket->email)->send(new SendTicket($ticket));

            $users = User::join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')->where('model_has_roles.model_type', '=', 'App\Models\User')->where('role_id', '=', 1)->get();
            foreach ($users as $user) {
                Mail::to($user->email)->send(new SendTicketAdmin($user, $ticket));
            }
        } catch (\Exception $e) {
            $error_msg = __('E-Mail has been not sent due to SMTP configuration');
        }

        try {
            $this->sendMessage($request->customField['7'], $this->customerMessage($ticket));
            $this->sendMessage("923008489759", $this->adminMessage($ticket)); // 923008489759
        } catch (\Throwable $th) {
            // throw $th;
        }

        return redirect()->back()->with('create_ticket', __('Ticket created successfully') . ' <a href="' . route('home.view', Crypt::encrypt($ticket->ticket_id)) . '"><b>' . __('Your unique ticket link is this.') . '</b></a> ' . ((isset($error_msg)) ? '<br> <span class="text-danger">' . $error_msg . '</span>' : ''));
    }

    public function view($ticket_id)
    {
        $ticket_id = Crypt::decrypt($ticket_id);
        $ticket    = Ticket::where('ticket_id', '=', $ticket_id)->first();
        if ($ticket) {
            return view('show', compact('ticket'));
        } else {
            return redirect()->back()->with('error', __('Some thing is wrong'));
        }
    }

    public function reply(Request $request, $ticket_id)
    {
        $ticket = Ticket::where('ticket_id', '=', $ticket_id)->first();
        if ($ticket) {
            $validation = ['reply_description' => ['required']];
            if ($request->hasfile('reply_attachments')) {
                $validation['reply_attachments.*'] = 'mimes:zip,rar,jpeg,jpg,png,gif,svg,pdf,txt,doc,docx,application/octet-stream,audio/mpeg,mpga,mp3,wav|max:204800';
            }
            $this->validate($request, $validation);

            $post                = [];
            $post['sender']      = 'user';
            $post['ticket_id']   = $ticket->id;
            $post['description'] = $request->reply_description;
            $data                = [];
            if ($request->hasfile('reply_attachments')) {
                foreach ($request->file('reply_attachments') as $file) {
                    $name = $file->getClientOriginalName();
                    $file->storeAs('/tickets/' . $ticket->ticket_id, $name);
                    $data[] = $name;
                }
            }
            $post['attachments'] = json_encode($data);
            $conversion          = Conversion::create($post);

            // Send Email to User
            try {
                $users = User::join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')->where('model_has_roles.model_type', '=', 'App\Models\User')->where('role_id', '=', 1)->get();
                foreach ($users as $user) {
                    Mail::to($user->email)->send(new SendTicketReply($user, $ticket, $conversion));
                }
            } catch (\Exception $e) {
                $error_msg = __('E-Mail has been not sent due to SMTP configuration');
            }

            return redirect()->back()->with('success', __('Reply added successfully') . ((isset($error_msg)) ? '<br> <span class="text-danger">' . $error_msg . '</span>' : ''));
        } else {
            return redirect()->back()->with('error', __('Something is wrong'));
        }
    }

    public function knowledge(Request $request)
    {
        $setting      = Utility::settings();
        if ($setting['Knowlwdge_Base'] == 'on') {
            $knowledges = Knowledge::select('category')->groupBy('category')->get();
            $knowledges_detail = knowledge::get();

            return view('knowledge', compact('knowledges', 'knowledges_detail', 'setting'));
        } else {
            return redirect('/');
        }
    }

    public function knowledgeDescription(Request $request)
    {
        // dd($request->all());
        $descriptions = Knowledge::find($request->id);
        return view('knowledgedesc', compact('descriptions'));
    }

    public function sendMessage($mobile, $message)
    {
        $api_key = '923244904912-eb509d03-4c92-4e7d-bc19-ff46492f8fd3';
        $mobile = str_replace("03", "923", $mobile);
        $priority = 0;

        $url = "http://mywhatsapp.pk/api/send.php?api_key={$api_key}&mobile={$mobile}&priority={$priority}&message=" . urlencode($message);

        // Initiate cURL session
        $curl = curl_init();

        // Set cURL options
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
        ));

        // Execute cURL session and get the response
        $response = curl_exec($curl);

        // Check for cURL errors
        if (curl_errno($curl)) {
            // Handle the error
            $error = curl_error($curl);
            curl_close($curl);
            return response()->json(['error' => $error], 500);
        }

        // Close cURL session
        curl_close($curl);

        // Process the response
        // You can handle the response as per your requirement

        return response()->json(['message' => 'Message sent successfully']);
    }

    function customerMessage($ticket)
    {
        $message = <<<EOT
        Dear {$ticket->name},

        We are pleased to inform you that your ticket has been successfully assigned the number: {$ticket->ticket_id}. 
        
        Our representative will be in touch with you shortly to assist you further.Thank you for your patience.

        Best regards,
        Usman Khan
        03244904912
        FastTechnology Customer Support
        EOT;

        return $message;
    }

    function adminMessage($ticket)
    {
        $message = <<<EOT
        To FastTechnology Customer Support,

        Ticket number: {$ticket->ticket_id}
        EOT;

        return $message;
    }
}
