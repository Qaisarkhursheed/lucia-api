<?php

namespace App\Events\Samples;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class AnyoneBroadcastEvent implements ShouldBroadcastNow
{
    use InteractsWithSockets, SerializesModels;


    public $message;

    /**
     * For pusher channels which allows only single channel
     * All events are visible to subscribers on public channels
     *
     * @var string
     */
    private string $event;

    public function __construct($message, string $event = "all" )
    {
        $this->message = $message;
        $this->event = $event;
    }

    public function broadcastOn()
    {
        // public channel, no authentication required
        return new Channel('lucia');
    }

    public function broadcastAs()
    {
        return $this->event;
    }
//
//    /**
//     * Get the data to broadcast.
//     * If this is used, it will send it as the data parameter
//     *  if not, it auto stringify public properties of this class and sends them
//     *
//     * @return array
//     */
//    public function broadcastWith()
//    {
//        return [
//            'id' => 444444,
//        ];
//    }
}
