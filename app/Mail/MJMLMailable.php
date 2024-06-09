<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Container\Container;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\HtmlString;

class MJMLMailable extends MailableBase
{

    /**
     * Build the message.
     *
     * @return \Illuminate\View\View|\Laravel\Lumen\Application
     * @throws \Exception
     */
    public function build(){
        throw new \Exception("Please, implement");
    }

    /**
     * @param string $mjml
     * @return string
     */
    private static function convertMjmlToHtml(string $mjml): string
    {
        //      working https://packagist.org/packages/qferr/mjml-php
        $renderer = new \Qferrer\Mjml\Renderer\BinaryRenderer(base_path('node_modules/.bin/mjml'));
        return $renderer->render($mjml );
    }

    /**
     * @inheritDoc
     */
    protected function buildView()
    {
        $mjml = Container::getInstance()->make('mailer')->render(
            $this->view, $this->viewData
        );

        $this->html = self::convertMjmlToHtml( $mjml );

        return array_filter([
            'html' => new HtmlString($this->html),
            'text' => $this->textView ?? null,
        ]);
    }

    /**
     * NB: if you use this, it will not allow the app to build data inside
     *
     * @param string $blade_name
     * @return MJMLMailable
     */
    protected function mjmlBlade(string $blade_name)
    {
        return $this->view( "mails.mjmls_blades." . $blade_name  );
    }
}
