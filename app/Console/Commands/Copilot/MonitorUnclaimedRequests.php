<?php

namespace App\Console\Commands\Copilot;

use App\Http\Controllers\Copilot\RequestMailResponseController;
use App\ModelsExtended\AdvisorRequest;
use App\ModelsExtended\AdvisorRequestStatus;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

/**
 */
class  MonitorUnclaimedRequests  extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'copilot:monitor-unclaimed-requests {request_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will move the request back to the pool if not claimed';

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
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // fetch all products
        $this->withProgressBar( $this->getAdvisorRequests(), function ( AdvisorRequest $request ){

            try {
                RequestMailResponseController::pushRequestBackToPool( $request );
                $this->info( sprintf( "\n %s: [ %s ] => DONE! \n", $request->id, $request->request_title) );
            }catch (\Exception $exception){
                $this->info( sprintf( "\n %s: [ %s ] => %s \n", $request->id, $request->request_title, $exception->getMessage() ) );
            }
        });

        $this->info( "\n-------------------------------------" );
        $this->info( "COMPLETED" );

        return true;
    }


    /**
     * @return array|Builder[]|Collection
     */
    private function getAdvisorRequests()
    {
        return AdvisorRequest::query()
            ->where( 'advisor_request_status_id' , AdvisorRequestStatus::PAID )
            ->whereDate( 'created_at' , "<", Carbon::now()->addHours(-5) )
            ->whereHas( 'advisor_assigned_copilot')
            ->when( $this->argument( 'request_id' ), function ( Builder $builder ){
                $builder->where( 'id' , $this->argument( 'request_id' ) );
            } )
            ->get();
    }
}
