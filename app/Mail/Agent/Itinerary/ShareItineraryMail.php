<?php

namespace App\Mail\Agent\Itinerary;

use App\Mail\MailableBase;
use App\ModelsExtended\Itinerary;
use App\ModelsExtended\ModelBase;
use Illuminate\Contracts\Queue\ShouldQueue;

class ShareItineraryMail extends MailableBase implements ShouldQueue
{

    /**
     * @var string
     */
    private string $htmlMessage;

    private Itinerary $itinerary;

    /**
     * Create a new message instance.
     * @param array $target_email_addresses
     * @param string|null $message
     * @param Itinerary|ModelBase $itinerary
     */
    public function __construct(array $target_email_addresses, ?string $message, Itinerary $itinerary )
    {
        $this->from( env( "MAIL_FROM_ADDRESS" ),  $itinerary->user->name );

        $this->subject =  "INVITATION | " .  $itinerary->title ;
        $this->htmlMessage = str_replace( [ "\r\n", "\n"  ], '<br/>', $message );
        $this->itinerary = $itinerary;

        // arrange the receiver so that they don't see each other
        // if more than one receiver is specified
        $this->to( count( $target_email_addresses ) > 1 ? $target_email_addresses[0]: $target_email_addresses );
        if( count( $target_email_addresses ) > 1  )
            $this->bcc( array_slice( $target_email_addresses, 1 ) );

        // Reply to the agent
        $this->replyTo( $itinerary->user->email,  $itinerary->user->name );
    }

    /**
     * Build the message.
     *
     * @return ShareItineraryMail
     */
    public function build()
    {
        return $this->view( "mails.agent.itinerary.share_itinerary_mail" )
            ->with( "htmlMessage" , $this->htmlMessage )
            ->with( "itinerary_logo_url" , optional($this->itinerary->itinerary_pictures->first())->image_url?? myAssetUrl('fallback-image.jpg') )
            ->with( "itinerary" , $this->itinerary );
    }
}
