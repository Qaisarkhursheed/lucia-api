<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class AdvisorChat
 *
 * @property int $id
 * @property int $chat_content_type_id
 * @property int $advisor_request_id
 * @property int $sender_id
 * @property int $receiver_id
 * @property string $plain_text
 * @property string|null $document_relative_url
 * @property bool $seen
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property bool $notified
 *
 * @property AdvisorRequest $advisor_request
 * @property ChatContentType $chat_content_type
 * @property User $user
 *
 * @package App\Models
 */
class AdvisorChat extends ModelBase
{
	protected $table = 'advisor_chat';

	protected $casts = [
		'chat_content_type_id' => 'int',
		'advisor_request_id' => 'int',
		'sender_id' => 'int',
		'receiver_id' => 'int',
		'seen' => 'bool',
		'notified' => 'bool'
	];

	protected $fillable = [
		'chat_content_type_id',
		'advisor_request_id',
		'sender_id',
		'receiver_id',
		'plain_text',
		'document_relative_url',
		'seen',
		'notified',
        'meeting_id',
        "file_size",
	];

	public function advisor_request()
	{
		return $this->belongsTo(AdvisorRequest::class);
	}

	public function chat_content_type()
	{
		return $this->belongsTo(ChatContentType::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'sender_id');
	}
    public function meeting()
    {
        return $this->belongsTo(Meeting::class,'meeting_id');
    }
}
