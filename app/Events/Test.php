<?php

namespace App\Events;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class Test extends Event
{
    use InteractsWithSockets, SerializesModels;


    public $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function broadcastOn()
    {
        // authentication required
        // manage auth on channels.php

        // you can use any channel name including prefix for identifiers
        // the subscriber just needs to match.
        return new PrivateChannel('lucia.12');
    }

    /**
     * By default, Laravel will broadcast the event using the event's class name.
     * However, you may customize the broadcast name by defining a broadcastAs method on the event:
     * Event: App\Events\SpecificBroadcastEvent
     *
     * @return string
     */
    public function broadcastAs()
    {
        return $this->message;
    }

//
//    /**
//     * Get the data to broadcast.
//     * If this is used, it will send it as the data parameter
//     *  if not, it auto stringify public properties of this class and sends them
//     *
//     * @return array
//     */
   public function broadcastWith()
   {
       return [
           'id' => 444444,
       ];
   }
}
