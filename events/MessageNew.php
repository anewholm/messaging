<?php namespace AcornAssociated\Messaging\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

use AcornAssociated\Messaging\Models\Message;

class MessageNew implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $user_from;
    public $contexts;

    public function __construct(Message $_message)
    {
        $this->message   = $_message;
        $this->user_from = $_message->user_from;
        $this->contexts  = $_message->contexts();
    } 

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        return [
            new Channel('messaging')
        ];
    }

    public function broadcastAs()
    {
        return 'message.new';
    }
}