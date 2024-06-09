<?php

namespace App\Console\Commands\Notifications;

use App\Mail\NewConciergeMessageMail;
use App\ModelsExtended\AdvisorChat;
use App\ModelsExtended\ChatContentType;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class  NotifyNewConciergeMessageCommand  extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:new-concierge-message';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will check for messages not seen and not marked notified and other than some minutes';

    const OLDER_THAN_MINUTES = 3;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param AdvisorChat $advisorChat
     * @return void
     */
    private static function notifyReceiver(AdvisorChat $advisorChat)
    {
        Mail::send( new NewConciergeMessageMail( $advisorChat ) );
        $advisorChat->notified = true;
        $advisorChat->updateQuietly();
    }

    /**
     * Execute the console command.
     *
     * @return bool
     */
    public function handle()
    {
        // fetch all products
        $this->withProgressBar( $this->getAdvisorChats(), function ( AdvisorChat $request ){

            try {
                self::notifyReceiver ( $request );

            }catch (Exception $exception){
                $this->error( sprintf( "\n %s: [ %s ] => %s \n", $request->id, $request->plain_text, $exception->getMessage() ) );
            }
        });

        $this->info( "\n-------------------------------------" );
        $this->info( "COMPLETED" );

        return true;
    }


    /**
     * @return array|Builder[]|Collection
     */
    private function getAdvisorChats()
    {
        return AdvisorChat::query()
            ->where( 'seen' , false )
            ->where( 'notified' , false )

            ->where( 'chat_content_type_id' , ChatContentType::TEXT )
            ->where( 'created_at' , '<', Carbon::now()->addMinutes(- self::OLDER_THAN_MINUTES ) )

            ->get();
    }
}
