<?php

namespace App\Console\Commands\TextractOcr;

use App\ModelsExtended\BookingOcr;
use App\ModelsExtended\OcrStatus;
use App\Repositories\TextractReader\Mediator;
use Illuminate\Console\Command;

class OcrInitializeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ocr:initialize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Move queued ocr to initialized state';

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
        $this->withProgressBar( $this->getQueuedOcrs(), function ( BookingOcr $ocr){
            $mediator = new Mediator();
            try {
                $ocr->recognition_hash_key = $mediator->recognize($ocr->s3_object_path );
                $ocr->setStatus( OcrStatus::INITIALIZED);
            }catch (\Exception $exception){
                $ocr->booking_ocr_recognition_logs()->create([
                    'ocr_status_id' => OcrStatus::FAILED_RECOGNITION,
                    'function_name' => __FUNCTION__,
                    'message' =>  $exception->getMessage(),
                ]);
                $ocr->setStatus( OcrStatus::FAILED_RECOGNITION );
            }
        });
        return 0;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|BookingOcr[]
     */
    private function getQueuedOcrs()
    {
        return BookingOcr::query()
            ->where("ocr_status_id", OcrStatus::QUEUED )
            ->get();
    }
}
