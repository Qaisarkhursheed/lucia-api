<?php

namespace App\ModelsExtended;

use App\ModelsExtended\Interfaces\IDeveloperPresentationInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * @property User $sender
 * @property User $receiver
 * @property AdvisorRequest $advisor_request
 */
class AdvisorChat extends \App\Models\AdvisorChat implements IDeveloperPresentationInterface
{
    protected $appends = [ 'document_url'  ];

    public function advisor_request()
    {
        return $this->belongsTo(AdvisorRequest::class);
    }

    /**
     * @param int $id
     * @return AdvisorChat
     */
    public static function getById( int $id )
    {
        return self::find($id);
    }

    /**
     * @return string
     */
    public function getDocumentUrlAttribute(): ?string
    {
        if( !$this->document_relative_url ) return $this->document_relative_url;
        return Storage::cloud()->url( $this->document_relative_url );
    }

    /**
     * Get traceable file name
     * @param UploadedFile $file
     * @param AdvisorRequest|ModelBase $advisorRequest
     * @return string
     */
    public static function generateRelativePath(UploadedFile $file, AdvisorRequest $advisorRequest): string
    {
        return sprintf( "%s/chats/%s", $advisorRequest->getFolderStorageRelativePath(), $file->hashName() );
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * @inheritDoc
     */
    public function presentForDev(): array
    {
        return [
            'id' => $this->id,
            'chat_content_type_id' => $this->chat_content_type_id,
            'chat_content_type' => isset($this->chat_content_type->description) ? $this->chat_content_type->description : '',
            'advisor_request_id' => $this->advisor_request_id,
            'sender_id' => $this->sender_id,
            'receiver_id' => $this->receiver_id,
            'plain_text' => $this->plain_text,
            'document_url' => $this->getDocumentUrlAttribute(),
            'seen' => $this->seen,
            'notified' => $this->notified,
            'created_at' => $this->created_at->toIso8601String(),
            'sentAt' => $this->getRequestPostedTime($this->created_at->toIso8601String()),
            'meeting' => $this->meeting,
            "file_size"=>$this->file_size,
            'sender' => [
                "first_name" => isset($this->sender->first_name) ? $this->sender->first_name : '',
                "last_name" => isset($this->sender->last_name) ? $this->sender->last_name : '',
                "profile_image_url" => isset($this->sender->profile_image_url) ? $this->sender->profile_image_url : '',
            ],
        ];
    }

    public function getRequestPostedTime($date){

        $date2 = date('Y-m-d');
        $date1 = date('Y-m-d',strtotime($date));
        $datetime1 = date_create($date1);
        $datetime2 = date_create($date2);
        $interval = date_diff($datetime1, $datetime2);
        $PostedString ='Today';
        if($interval->y > 0){
            $PostedString = $interval->y.' Year(s)';
            $PostedString .= ($interval->m > 0)?' and '.$interval->m. ' Month(s)':'';
            $PostedString .=' Ago';
        }elseif($interval->m > 0){
            $PostedString = $interval->m.' month(s)';
            $PostedString .= ($interval->d > 0)?' and '.$interval->d. ' Day(s)':'';
            $PostedString .=' Ago';
        }elseif($interval->d > 0){
            $PostedString = $interval->d.' Day(s)';
            $PostedString .=' Ago';
        }else{
            $PostedString = 'Today';
        }

        return $PostedString;
    }
}
