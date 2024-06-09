<?php


namespace App\Repositories\TextractReader;

use App\ModelsExtended\BookingOcr;
use App\ModelsExtended\OcrStatus;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class AwsOcrQueues
{
    /**
     * @var Filesystem
     */
    private Filesystem $disk;

    public function __construct()
    {
        $this->disk = Storage::disk('textract');
    }

    /**
     * Upload file like PDF2.pdf to textract s3 server
     *
     * @param string $s3_object_path
     * @param string $content
     * @return string
     */
    public function uploadFileToS3(string $s3_object_path, string $content):string
    {
        $this->disk->put( $s3_object_path, $content );
        return $s3_object_path;
    }

    /**
     * Add a file to the queue for processing
     *
     * @param int $created_by_id
     * @param int $itinerary_id
     * @param string $s3_object_path
     * @param string|null $fileName
     * @return Builder|Model
     */
    public function addFileToQueue( int $created_by_id, int $itinerary_id,
                                           string $s3_object_path, ?string $fileName = null )
    {
        return BookingOcr::query()->create([
            'file_name' => $fileName?? basename( $s3_object_path ),
            's3_object_path' => $s3_object_path,
            'document_model_type' => null,
            'ocr_status_id' => OcrStatus::QUEUED,
            'itinerary_id' => $itinerary_id,
            'created_by_id' => $created_by_id
        ]);
    }

    /**
     * @param string $s3_object_path
     * @return string
     */
    public function fileUrl(string $s3_object_path): string
    {
        return $this->disk->url($s3_object_path);
    }

    /**
     * @param string $s3_object_path
     * @return bool
     */
    public function deleteFile(string $s3_object_path): bool
    {
        return $this->disk->delete($s3_object_path);
    }
}
