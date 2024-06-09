<?php

namespace App\Jobs;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Mail\NewRequestReceivedForAllMail;
use Illuminate\Support\Facades\Mail;

class NewRequestReceivedForAllJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $advisorRequest;
    protected $copilots;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($advisorRequest, $copilots)
    {
        $this->advisorRequest = $advisorRequest;
        $this->copilots = $copilots;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach($this->copilots as $coPilot)
            {
                if($coPilot->isCopilot() && $coPilot->isApproved())
                {
                    Mail::send( new NewRequestReceivedForAllMail ( $this->advisorRequest, $coPilot ) );
                }
            }
    }
}
