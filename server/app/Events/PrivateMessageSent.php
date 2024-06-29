<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PrivateMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $receiverId;

    public $senderId;

    public function __construct($message, $receiverId , $senderId)
    {
        $this->message = $message;
        $this->receiverId = $receiverId;
        $this->senderId = $senderId;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('private-chat.' . $this->receiverId);
    }

    public function broadcastWith()
    {
        return ['message' => $this->message , 'receiver_id' => $this->receiverId , 'senderId' => $this->senderId];
    }
}
