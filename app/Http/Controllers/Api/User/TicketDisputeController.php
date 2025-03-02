<?php

namespace App\Http\Controllers\Api\User;

use App\Classes\GeniusMailer;
use App\Http\Controllers\Controller;
use App\Http\Resources\TicketDisputeMessageResource;
use App\Http\Resources\TicketDisputeResource;
use App\Models\AdminUserConversation;
use App\Models\AdminUserMessage;
use App\Models\Generalsetting;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Pagesetting;
use Illuminate\Http\Request;
use Validator;

class TicketDisputeController extends Controller
{
    public function tickets()
    {
        try {
            return response()->json(['status' => true, 'data' => TicketDisputeResource::collection(AdminUserConversation::where('user_id', auth()->user()->id)->where('type', 'Ticket')->get()), 'error' => []]);
        } catch (\Exception $e) {
            return response()->json(['status' => true, 'data' => [], 'error' => ['message' => $e->getMessage()]]);
        }
    }

    public function disputes()
    {
        try {
            return response()->json(['status' => true, 'data' => TicketDisputeResource::collection(AdminUserConversation::where('user_id', auth()->user()->id)->where('type', 'Dispute')->get()), 'error' => []]);
        } catch (\Exception $e) {
            return response()->json(['status' => true, 'data' => [], 'error' => ['message' => $e->getMessage()]]);
        }
    }

    public function store(Request $request)
    {
        try {
            $rules = [
                'subject' => 'required',
                'message' => 'required',
                'type' => 'required',
                'order_number' => [
                    function ($attribute, $value, $fail) use ($request) {
                        if ($request->type == 'Dispute') {
                            if (empty($request->order_number)) {
                                $fail('The order number field is required');
                            }
                        }
                    },
                ],
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['status' => false, 'data' => [], 'error' => $validator->errors()]);
            }


            if ($request->type ==  'Dispute') {
                $order = Order::where('order_number', $request->order_number)->exists();
                if (!$order) {
                    return response()->json(['status' => false, 'data' => [], 'error' => ["order_number" => ["Order Number Not Found"]]]);
                }
            }

            $type = $request->type;
            $checkType = in_array($type, ['Ticket', 'Dispute']);
            if (!$checkType) {
                return response()->json(['status' => false, 'data' => [], 'error' => ["message" => "This type doesn't exists."]]);
            }

            $user = auth()->user();
            $gs = Generalsetting::find(1);
            $subject = $request->subject;
            $to = Pagesetting::find(1)->contact_email;
            $from = $user->email;
            $msg = "Email: " . $from . "\nMessage: " . $request->message;
            if ($gs->is_smtp == 1) {
                $data = [
                    'to' => $to,
                    'subject' => $subject,
                    'body' => $msg,
                ];

                $mailer = new GeniusMailer();
                $mailer->sendCustomMail($data);
            } else {
                $headers = "From: " . $gs->from_name . "<" . $gs->from_email . ">";
                mail($to, $subject, $msg, $headers);
            }

            if ($request->type == 'Ticket') {
                $conv = AdminUserConversation::where('type', '=', 'Ticket')->where('user_id', '=', $user->id)->where('subject', '=', $subject)->first();
            } else {
                $conv = AdminUserConversation::where('type', '=', 'Dispute')->where('user_id', '=', $user->id)->where('subject', '=', $subject)->first();
            }

            if (isset($conv)) {

                $msg = new AdminUserMessage();
                $msg->conversation_id = $conv->id;
                $msg->message = $request->message;
                $msg->user_id = $user->id;
                $msg->save();
                return response()->json(['status' => true, 'data' => new TicketDisputeMessageResource($msg), 'error' => []]);
            } else {
                $message = new AdminUserConversation();
                $message->subject = $subject;
                $message->user_id = $user->id;
                $message->message = $request->message;
                $message->order_number = $request->order_number;
                $message->type = $request->type;
                $message->save();

                $notification = new Notification;
                $notification->conversation_id = $message->id;
                $notification->save();

                $msg = new AdminUserMessage();
                $msg->conversation_id = $message->id;
                $msg->message = $request->message;
                $msg->user_id = $user->id;
                $msg->save();
                return response()->json(['status' => true, 'data' => new TicketDisputeResource($message), 'error' => []]);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => true, 'data' => [], 'error' => ['message' => $e->getMessage()]]);
        }
    }

    public function delete($id)
    {
        try {
            $conv = AdminUserConversation::find($id);

            if (!$conv) {
                return response()->json(['status' => false, 'data' => [], 'error' => ["message" => "Not found."]]);
            }

            if ($conv->messages->count() > 0) {
                foreach ($conv->messages as $key) {
                    $key->delete();
                }
            }
            $conv->delete();
            return response()->json(['status' => true, 'data' => ['message' => 'Message Deleted Successfully!'], 'error' => []]);
        } catch (\Exception $e) {
            return response()->json(['status' => true, 'data' => [], 'error' => ['message' => $e->getMessage()]]);
        }
    }

    public function messageStore(Request $request)
    {
        try {
            $rules =
                [
                    'user_id' => 'required',
                    'message' => 'required',
                    'conversation_id' => 'required',
                ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['status' => false, 'data' => [], 'error' => $validator->errors()]);
            }

            $msg = new AdminUserMessage();
            $input = $request->all();
            $input['user_id'] = auth()->user()->id;
            $msg->fill($input)->save();
            $notification = new Notification;
            $notification->conversation_id = $msg->conversation->id;
            $notification->save();
            //--- Redirect Section

            return response()->json(['status' => true, 'data' => new TicketDisputeMessageResource($msg), 'error' => []]);
            //--- Redirect Section Ends
        } catch (\Exception $e) {
            return response()->json(['status' => true, 'data' => [], 'error' => ['message' => $e->getMessage()]]);
        }
    }
}
