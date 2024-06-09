<?php

namespace App\Console\Commands\TextractOcr;

use App\ModelsExtended\BookingOcr;
use App\ModelsExtended\OcrStatus;
use App\Repositories\TextractReader\Mediator;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Artisan;

class OcrRecognizeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ocr:recognize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor initialized ocr to completed recognition state';

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
     * @return int
     */
    public function handle()
    {
        $this->withProgressBar( $this->getOcrs(), function ( BookingOcr $ocr){
            $mediator = new Mediator();
            try {
                if( $mediator->status($ocr->recognition_hash_key )->status === "Completed" )
                {
                    $ocr->booking_ocr_recognition_logs()->create([
                        'ocr_status_id' => OcrStatus::COMPLETED_RECOGNITION,
                        'function_name' => __FUNCTION__,
                        'message' =>  null,
                        'api_response' =>  (array)$mediator->getData()->data,
                    ]);
                    $ocr->setStatus( OcrStatus::COMPLETED_RECOGNITION );

                    $mediator->acknowledged( $ocr->recognition_hash_key );

                }else{
                    $ocr->setStatus( OcrStatus::RECOGNIZING );
                }
            }catch (\Exception $exception){
                $ocr->booking_ocr_recognition_logs()->create([
                    'ocr_status_id' => OcrStatus::FAILED_RECOGNITION,
                    'function_name' => __FUNCTION__,
                    'message' =>  $exception->getMessage(),
                ]);
                $ocr->setStatus( OcrStatus::FAILED_RECOGNITION );
            }
        });

        Artisan::call( "ocr:import" );
        return 0;
    }

    /**
     * @return Builder[]|Collection|BookingOcr[]
     */
    private function getOcrs()
    {
        return BookingOcr::query()
            ->whereIn("ocr_status_id", [OcrStatus::INITIALIZED, OcrStatus::RECOGNIZING ] )
            ->get();
    }
}
