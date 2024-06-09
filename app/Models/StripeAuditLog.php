<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class StripeAuditLog
 * 
 * @property int $id
 * @property int $user_id
 * @property string $action_required
 * @property array|null $request_params
 * @property array|null $stripe_response
 * @property string|null $comments
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property User $user
 *
 * @package App\Models
 */
class StripeAuditLog extends ModelBase
{
	protected $table = 'stripe_audit_logs';

	protected $casts = [
		'user_id' => 'int',
		'request_params' => 'json',
		'stripe_response' => 'json'
	];

	protected $fillable = [
		'user_id',
		'action_required',
		'request_params',
		'stripe_response',
		'comments'
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
