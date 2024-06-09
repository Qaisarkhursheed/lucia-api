<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MailableBase extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param string $subject
     * @return string
     */
    public function ucwordsSubject(string $subject ): string
    {
        return collect(explode(" | ", $subject))
            ->map(fn( string $word ) => ucfirst(strtolower( $word )))
            ->implode(" | ");
    }

}
