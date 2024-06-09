<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class AdvisorRequest
 *
 * @property int $id
 * @property Carbon|null $deadline
 * @property int|null $itinerary_id
 * @property int $advisor_request_status_id
 * @property string|null $notes
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property int $created_by_id
 * @property string|null $request_title
 * @property float $sub_amount
 * @property float $fee_amount
 * @property float $total_amount
 * @property string|null $discount_code
 * @property float $discount
 *
 * @property User $user
 * @property Itinerary|null $itinerary
 * @property AdvisorRequestStatus $advisor_request_status
 * @property AdvisorAssignedCopilot $advisor_assigned_copilot
 * @property Collection|AdvisorChat[] $advisor_chats
 * @property Collection|AdvisorRequestActivity[] $advisor_request_activities
 * @property Collection|AdvisorRequestAttachment[] $advisor_request_attachments
 * @property AdvisorRequestFeedback $advisor_request_feedback
 * @property AdvisorRequestPayment $advisor_request_payment
 * @property Collection|AdvisorRequestTask[] $advisor_request_tasks
 *
 * @package App\Models
 */
class AdvisorRequest extends ModelBase
{
	protected $table = 'advisor_request';

	protected $casts = [
		'itinerary_id' => 'int',
		'advisor_request_status_id' => 'int',
		'created_by_id' => 'int',
		'sub_amount' => 'float',
		'fee_amount' => 'float',
		'total_amount' => 'float',
		'discount' => 'float'
	];

	protected $dates = [
		'deadline'
	];

	protected $fillable = [
		'deadline',
		'itinerary_id',
		'advisor_request_status_id',
		'notes',
		'created_by_id',
		'request_title',
		'sub_amount',
		'fee_amount',
		'total_amount',
		'discount_code',
		'discount',
        'completed_at',
        "request_type",
	];
    protected $with = [
        'user',
        'advisor_chats',
        'advisor_assigned_copilot',

    ];

	public function user()
	{
		return $this->belongsTo(User::class, 'created_by_id');
	}

	public function itinerary()
	{
		return $this->belongsTo(Itinerary::class);
	}

	public function advisor_request_status()
	{
		return $this->belongsTo(AdvisorRequestStatus::class);
	}

	public function advisor_assigned_copilot()
	{
		return $this->hasOne(AdvisorAssignedCopilot::class);
	}

	public function advisor_chats()
	{
		return $this->hasMany(AdvisorChat::class);
	}

	public function advisor_request_activities()
	{
		return $this->hasMany(AdvisorRequestActivity::class);
	}

	public function advisor_request_attachments()
	{
		return $this->hasMany(AdvisorRequestAttachment::class);
	}

	public function advisor_request_feedback()
	{
		return $this->hasOne(AdvisorRequestFeedback::class);
	}

	public function advisor_request_payment()
	{
		return $this->hasOne(AdvisorRequestPayment::class);
	}

	public function advisor_request_tasks()
	{
		return $this->hasMany(AdvisorRequestTask::class);
	}
}
