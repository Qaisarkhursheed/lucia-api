<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class ChatContentType
 * 
 * @property int $id
 * @property string $description
 * 
 * @property Collection|AdvisorChat[] $advisor_chats
 *
 * @package App\Models
 */
class ChatContentType extends ModelBase
{
	protected $table = 'chat_content_type';
	public $timestamps = false;

	protected $fillable = [
		'description'
	];

	public function advisor_chats()
	{
		return $this->hasMany(AdvisorChat::class);
	}
}
