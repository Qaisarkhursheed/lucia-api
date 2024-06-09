<?php

namespace App\View\Components;

class AppEmailBigButton extends \Illuminate\View\Component
{
    private string $bgcolor;
    private string $link;

    public function __construct( $bgcolor = '#BA886E', $link = "#" )
    {
        $this->bgcolor = $bgcolor;
        $this->link = $link;
    }

    /**
     * @inheritDoc
     */
    public function render()
    {
        return view('mails.components.app_email_big_button')
            ->with(  "bgcolor" , $this->bgcolor )
            ->with(  "link" , $this->link );
    }
}
