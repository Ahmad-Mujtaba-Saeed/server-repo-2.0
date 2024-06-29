<?php

namespace App\Http\Controllers;

use App\Models\messages;
use Illuminate\Http\Request;
use App\Events\MessageSent;
use App\Events\PrivateMessageSent;
use Carbon\Carbon;

class ChatController extends Controller
{
    public function sendMessage(Request $request)
    {
        $message = ['username' => $request->username, 'message' => $request->message];
        broadcast(new MessageSent($message))->toOthers();
        return response()->json(['status' => 'Message Sent!']);
    }


    public function MessageStore(Request $request){
        $user = $request->user();
        $receiverId = (int)($request->input('receiver_id'));
        $sender_id = $user->id;
        $messageSent = messages::where(function ($query) use ($receiverId, $sender_id) {
            $query->where('Receiving_id', $receiverId)
                  ->where('Sending_id', $sender_id);
        })
        ->orWhere(function ($query) use ($receiverId, $sender_id) {
            $query->where('Sending_id', $receiverId)
                  ->where('Receiving_id', $sender_id);
        })
        ->get();
        return $messageSent;
    }



    public function PrivateMessage(Request $request)
    {
        $message = $request->input('message');
        $receiverId = (int)($request->input('receiver_id'));

        $currentDateTime = Carbon::now();
        $date = $currentDateTime->format('Y-m-d');
        $time = $currentDateTime->format('H:i:s');

        $user = $request->user();

        
        
            $messageSent = messages::create([
                'Sending_id' => $user->id,
                'Message' => $message,
                'Receiving_id' => $receiverId,
                'Date' => $date,
                'time' => $time
            ]);
            if(!$messageSent){
                return response()->json(['success' => false , 'message' => 'Failed to upload message to Database']);
            }
            $messageEvent = event(new PrivateMessageSent($message, $receiverId));
            
            return response()->json(['success' => true , 'message' => 'Message sent successfully']);
}
}