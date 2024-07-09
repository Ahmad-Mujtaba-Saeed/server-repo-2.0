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


    public function MessageStore(Request $request)
    {
        $user = $request->user();
        $receiverId = (int) ($request->input('receiver_id'));
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
        $receiverId = (int) ($request->input('receiver_id'));

        $currentDateTime = Carbon::now();
        $date = $currentDateTime->format('Y-m-d');
        $time = $currentDateTime->format('H:i:s');

        $user = $request->user();

        $sender_id = $user->id;

        $messageSent = messages::create([
            'Sending_id' => $sender_id,
            'Message' => $message,
            'Receiving_id' => $receiverId,
            'Date' => $date,
            'time' => $time
        ]);
        if (!$messageSent) {
            return response()->json(['success' => false, 'message' => 'Failed to upload message to Database']);
        }
        $messageEvent = event(new PrivateMessageSent($message, $receiverId, $sender_id));

        return response()->json(['success' => true, 'message' => 'Message sent successfully']);
    }
    public function GetEachStoredMessages(Request $request)
    {
        $user = $request->user();
        $ID = $user->id;
        $ID = $user->id;

        // Get the latest message for each distinct sending_id
        $subQuery = messages::select(\DB::raw('MAX(id) as id'))
            ->where('Receiving_id', $ID)
            ->groupBy('Sending_id');

        $EachMessages = messages::whereIn('id', $subQuery->pluck('id'))
            ->with('sender.images') // Assuming you want to eager load the sender details
            ->get();
            foreach ($EachMessages as $Message) {
                if (isset($Message->sender->images[0])) {
                    $imgPath = $Message->sender->images[0]->ImageName;
                    $data = base64_encode(file_get_contents(public_path($imgPath)));
                    $Message->sender->images[0]->setAttribute('data', $data);
                }
            }

        return response()->json(['success' => true, 'data' => $EachMessages]);
    }
}