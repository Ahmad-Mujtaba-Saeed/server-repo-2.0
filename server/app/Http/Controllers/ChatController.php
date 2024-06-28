<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\MessageSent;
use App\Events\PrivateMessageSent;

class ChatController extends Controller
{
    public function sendMessage(Request $request)
    {
        $message = ['username' => $request->username, 'message' => $request->message];
        broadcast(new MessageSent($message))->toOthers();
        return response()->json(['status' => 'Message Sent!']);
    }
    public function PrivateMessage(Request $request)
    {
        $message = $request->input('message');
        $receiverId = $request->input('receiver_id');

        event(new PrivateMessageSent($message, $receiverId));

        return response()->json(['status' => 'Message Sent!']);
    }
}
