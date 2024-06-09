<?php

namespace App\Console\Commands\TextractOcr;

use App\Jobs\OcrImportJob;
use App\ModelsExtended\BookingOcr;
use App\ModelsExtended\OcrStatus;
use App\Repositories\TextractReader\Mediator;
use Illuminate\Console\Command;

class OcrImportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ocr:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Move completed recognition ocr to importing state';

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
            try {

                $ocr->setStatus( OcrStatus::IMPORTING);
                dispatch( new OcrImportJob($ocr) );

            }catch (\Exception $exception){
                $ocr->booking_ocr_importation_logs()->create([
                    'function_name' => __FUNCTION__,
                    'log' =>  $exception->getMessage(),
                ]);
                $ocr->setStatus( OcrStatus::FAILED_IMPORTATION );
            }
        });
        return 0;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|BookingOcr[]
     */
    private function getOcrs()
    {
        return BookingOcr::query()
            ->where("ocr_status_id", OcrStatus::COMPLETED_RECOGNITION )
            ->get();
    }
}
