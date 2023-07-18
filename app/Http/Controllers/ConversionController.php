<?php

namespace App\Http\Controllers;

use App\Models\Conversion;
use App\Mail\SendTicketAdminReply;
use App\Models\CustomFieldValue;
use App\Models\Ticket;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ConversionController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $ticket_id)
    {

        $user = \Auth::user();
        if (!$user || $user->can('reply-tickets')) {
            $ticket = Ticket::find($ticket_id);
            if ($ticket) {
                $validation = ['reply_description' => ['required']];

                $this->validate($request, $validation);

                $post = [];
                $post['sender'] = ($user) ? $user->id : 'user';
                $post['ticket_id'] = $ticket->id;
                $post['description'] = $request->reply_description;
                $data = [];
                if ($request->hasfile('reply_attachments')) {
                    $errors = [];
                    foreach ($request->file('reply_attachments') as $filekey => $file) {
                        $name = $file->getClientOriginalName();
                        $dir        = ('reply_tickets/' . $post['ticket_id']);
                        $path = Utility::multipalFileUpload($request, 'reply_attachments', $name, $dir, $filekey, []);

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
                $conversion = Conversion::create($post);

                // Send Email to User
                $uArr = [
                    'name' => $request->name,
                    'ticket_id' => $ticket->id,
                    'email' => $ticket->email,
                    'description' => $request->reply_description,
                ];

                try {

                    Mail::to($ticket->email)->send(new SendTicketAdminReply($ticket, $conversion));
                } catch (\Exception $e) {
                    $error_msg = "E-Mail has been not sent due to SMTP configuration ";
                }

                try {
                    $custom_field_value = CustomFieldValue::where('record_id', $ticket_id)->where('field_id', 7)->first();
                    $this->sendMessage($custom_field_value->value, $this->customerMessage($ticket, strip_tags($request->reply_description)));
                } catch (\Throwable $th) {
                    throw $th;
                }
                return redirect()->back()->with('success', __('Reply added successfully') . ((isset($error_msg)) ? '<br> <span class="text-danger">' . $error_msg . '</span>' : ''));
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
     * @param  \App\Conversion  $conversion
     * @return \Illuminate\Http\Response
     */
    public function show(Conversion $conversion)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Conversion  $conversion
     * @return \Illuminate\Http\Response
     */
    public function edit(Conversion $conversion)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Conversion  $conversion
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Conversion $conversion)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Conversion  $conversion
     * @return \Illuminate\Http\Response
     */
    public function destroy(Conversion $conversion)
    {
        //
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


    function customerMessage($ticket, $message)
    {
        $message = <<<EOT
        Dear {$ticket->name},

        {$message}

        Best regards,
        Usman Khan
        03244904912
        FastTechnology Customer Support
        EOT;

        return $message;
    }
}
