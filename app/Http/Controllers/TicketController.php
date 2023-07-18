<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CustomField;
use App\Mail\SendCloseTicket;
use App\Mail\SendTicket;
use App\Models\Ticket;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use App\Exports\TicketsExport;
use App\Models\CustomFieldValue;
use Maatwebsite\Excel\Facades\Excel;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($status = '')
    {
        $user = \Auth::user();

        if ($user->can('manage-tickets')) {
            $tickets = Ticket::select(
                [
                    'tickets.*',
                    'categories.name as category_name',
                    'categories.color',
                ]
            )->join('categories', 'categories.id', '=', 'tickets.category');
            if ($status == 'in-progress') {
                $tickets->where('status', '=', 'In Progress');
            } elseif ($status == 'on-hold') {
                $tickets->where('status', '=', 'On Hold');
            } elseif ($status == 'closed') {
                $tickets->where('status', '=', 'Closed');
            }
            $tickets = $tickets->orderBy('id', 'desc')->get();

            return view('admin.tickets.index', compact('tickets', 'status'));
        } else {
            return view('403');
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = \Auth::user();
        if ($user->can('create-tickets')) {
            $customFields = CustomField::where('id', '>', '6')->get();

            $categories = Category::get();

            return view('admin.tickets.create', compact('categories', 'customFields'));
        } else {
            return view('403');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = \Auth::user();
        if ($user->can('create-tickets')) {
            $validation = [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255',
                'category' => 'required|string|max:255',
                'subject' => 'required|string|max:255',
                'status' => 'required|string|max:100',
                'description' => 'required',
            ];

            $this->validate($request, $validation);

            $post              = $request->all();
            $post['ticket_id'] = time();
            $post['created_by'] = \Auth::user()->createId();
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

                'name' => $request->name,
                'email' => $user->email,
                'category' => $request->category,
                'subject' => $request->subject,
                'status' => $request->status,
                'description' => $request->description,
            ];

            $resp = Utility::sendEmailTemplate('new_ticket', [$user->id => $user->email], $uArr);
            // dd($resp);

            try {
                $this->sendMessage($request->customField['7'], $this->customerMessage($ticket));
                $this->sendMessage("923008489759", $this->adminMessage($ticket)); // 923008489759
            } catch (\Throwable $th) {
                // throw $th;
            }

            // Send Email to
            if (isset($error_msg)) {
                Session::put('smtp_error', '<span class="text-danger ml-2">' . $error_msg . '</span>');
            }
            Session::put('ticket_id', ' <a class="text text-primary" target="_blank" href="' . route('home.view', \Illuminate\Support\Facades\Crypt::encrypt($ticket->ticket_id)) . '"><b>' . __('Your unique ticket link is this.') . '</b></a>');

            return redirect()->route('admin.tickets.index')->with('success', __('Ticket created successfully'));
        } else {
            return view('403');
        }
    }

    public function storeNote($ticketID, Request $request)
    {
        $user = \Auth::user();
        if ($user->can('reply-tickets')) {
            $validation = [
                'note' => ['required'],
            ];
            $this->validate($request, $validation);

            $ticket = Ticket::find($ticketID);
            if ($ticket) {
                $ticket->note = $request->note;
                $ticket->save();

                return redirect()->back()->with('success', __('Ticket note saved successfully'));
            } else {
                return view('403');
            }
        } else {
            return view('403');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Ticket $ticket
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Ticket $ticket)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Ticket $ticket
     *
     * @return \Illuminate\Http\Response
     */
    public function editTicket($id)
    {
        $user = \Auth::user();
        if ($user->can('edit-tickets')) {
            $ticket = Ticket::find($id);
            if ($ticket) {
                $customFields        = CustomField::where('id', '>', '6')->get();
                $ticket->customField = CustomField::getData($ticket);
                $categories          = Category::get();

                return view('admin.tickets.edit', compact('ticket', 'categories', 'customFields'));
            } else {
                return view('403');
            }
        } else {
            return view('403');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Ticket $ticket
     *
     * @return \Illuminate\Http\Response
     */
    public function updateTicket(Request $request, $id)
    {
        $user = \Auth::user();
        if ($user->can('edit-tickets')) {
            $ticket = Ticket::find($id);
            if ($ticket) {
                $validation = [
                    'name' => 'required|string|max:255',
                    'email' => 'required|string|email|max:255',
                    'category' => 'required|string|max:255',
                    'subject' => 'required|string|max:255',
                    'status' => 'required|string|max:100',
                    'description' => 'required',
                ];

                $this->validate($request, $validation);

                $post = $request->all();
                if ($request->hasfile('attachments')) {
                    $data = json_decode($ticket->attachments, true);
                    foreach ($request->file('attachments') as $filekey => $file) {
                        $name = $file->getClientOriginalName();
                        $file->storeAs('tickets/' . $ticket->ticket_id, $name);
                        $data[] = $name;
                        $url = '';
                        $dir        = ('tickets/' . $ticket->ticket_id);
                        $path = Utility::multipalFileUpload($request, 'attachments', $name, $dir, $filekey, []);
                        if ($path['flag'] == 1) {
                            $url = $path['url'];
                        } else {
                            return redirect()->route('admin.tickets.store', \Auth::user()->id)->with('error', __($path['msg']));
                        }
                    }
                    $post['attachments'] = json_encode($data);
                }
                $ticket->update($post);
                CustomField::saveData($ticket, $request->customField);

                $error_msg = '';
                if ($ticket->status == 'Closed') {

                    try {
                        $custom_field_value = CustomFieldValue::where('record_id', $ticket->id)->where('field_id', 7)->first();
                        $this->sendMessage($custom_field_value->value, $this->closeMessage($ticket));
                    } catch (\Throwable $th) {
                        throw $th;
                    }

                    // Send Email to User
                    try {
                        Mail::to($ticket->email)->send(new SendCloseTicket($ticket));
                    } catch (\Exception $e) {
                        $error_msg = "E-Mail has been not sent due to SMTP configuration ";
                    }
                }

                return redirect()->back()->with('success', __('Ticket updated successfully.') . ((isset($error_msg) && !empty($error_msg)) ? '<span class="text-danger">' . $error_msg . '</span>' : ''));
            } else {
                return view('403');
            }
        } else {
            return view('403');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Ticket $ticket
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = \Auth::user();
        if ($user->can('edit-tickets')) {
            $ticket = Ticket::find($id);
            $ticket->delete();

            return redirect()->back()->with('success', __('Ticket deleted successfully'));
        } else {
            return view('403');
        }
    }

    public function attachmentDestroy($ticket_id, $id)
    {
        $user = \Auth::user();
        if ($user->can('edit-tickets')) {
            $ticket      = Ticket::find($ticket_id);
            $attachments = json_decode($ticket->attachments);
            if (isset($attachments[$id])) {
                if (asset(Storage::exists('tickets/' . $ticket->ticket_id . "/" . $attachments[$id]))) {
                    asset(Storage::delete('tickets/' . $ticket->ticket_id . "/" . $attachments[$id]));
                }
                unset($attachments[$id]);
                $ticket->attachments = json_encode(array_values($attachments));
                $ticket->save();

                return redirect()->back()->with('success', __('Attachment deleted successfully'));
            } else {
                return redirect()->back()->with('error', __('Attachment is missing'));
            }
        } else {
            return view('403');
        }
    }

    public function export()
    {
        $name = 'Tickets' . date('Y-m-d i:h:s');
        $data = Excel::download(new TicketsExport(), $name . '.csv');

        return $data;
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

    function closeMessage($ticket)
    {
        $message = <<<EOT
        Dear {$ticket->name},

        We are delighted to hear that your recent complaint has been resolved to your satisfaction. We value your feedback and would greatly appreciate it if you could take a moment to share your experience by leaving a review for us on Google. 
        Review : https://g.page/r/CUDVz2KdYAi2EBE/review

        Thank you once again for choosing fasttechnologies.pk and for taking the time to provide us with your valuable feedback. We look forward to serving you in the future.

        Best regards,
        Usman Khan
        03244904912
        FastTechnology Customer Support
        EOT;

        return $message;
    }
}
