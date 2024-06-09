<?php

namespace App\View\Components;

class AppEmailParagraph extends \Illuminate\View\Component
{
    /**
     * @inheritDoc
     */
    public function render()
    {
        return view('mails.components.app_email_paragraph');
    }
}
