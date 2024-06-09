<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $first_name
 * @property string $last_name
 * @property string|null $profile_image_url
 * @property string|null $phone
 * @property string|null $location
 * @property string|null $iata_number
 * @property string|null $agency_name
 * @property string|null $job_title
 * @property string $preferred_timezone_tzab
 * @property string|null $password_reset_token
 * @property Carbon|null $password_reset_token_expiry
 * @property int $account_status_id
 * @property string $friendly_identifier
 * @property int $default_currency_id
 * @property string|null $impersonation_token
 * @property Carbon|null $impersonation_token_expiry
 * @property array|null $google_authentication_token
 * @property string|null $linkedin_url
 * @property int|null $agency_usage_mode_id
 * @property string|null $address_line1
 * @property string|null $address_line2
 * @property string|null $state
 * @property string|null $zip
 * @property string|null $city
 * @property string|null $deleted_at
 * @property int $country_id
 * @property bool $is_virtuoso_member
 *
 * @property AccountStatus $account_status
 * @property AgencyUsageMode|null $agency_usage_mode
 * @property Country $country
 * @property CurrencyType $currency_type
 * @property Collection|AdvisorAssignedCopilot[] $advisor_assigned_copilots
 * @property Collection|AdvisorChat[] $advisor_chats
 * @property Collection|AdvisorRequest[] $advisor_requests
 * @property Collection|BookingOcr[] $booking_ocrs
 * @property ClientInfo $client_info
 * @property Collection|CopilotDuty[] $copilot_duties
 * @property CopilotInfo $copilot_info
 * @property DefaultItineraryTheme $default_itinerary_theme
 * @property Collection|Itinerary[] $itineraries
 * @property Collection|MasterAccount[] $master_accounts
 * @property MasterAccount $master_account
 * @property Collection|MasterSubAccount[] $master_sub_accounts
 * @property MasterSubAccount $master_sub_account
 * @property Collection|SavedSupplier[] $saved_suppliers
 * @property Collection|ServiceSupplier[] $service_suppliers
 * @property Collection|StripeAuditLog[] $stripe_audit_logs
 * @property Collection|StripeCheckoutLog[] $stripe_checkout_logs
 * @property Collection|StripeConnectReminder[] $stripe_connect_reminders
 * @property Collection|StripePaymentIntent[] $stripe_payment_intents
 * @property Collection|StripeSubscriptionHistory[] $stripe_subscription_histories
 * @property Collection|Traveller[] $travellers
 * @property Collection|UserNote[] $user_notes
 * @property Collection|Role[] $roles
 * @property Collection|UserSession[] $user_sessions
 * @property UserStripeAccount $user_stripe_account
 *
 * @package App\Models
 */
class User extends ModelBase
{
	use SoftDeletes;
	protected $table = 'users';

	protected $casts = [
		'account_status_id' => 'int',
		'default_currency_id' => 'int',
		'google_authentication_token' => 'json',
		'agency_usage_mode_id' => 'int',
		'country_id' => 'int',
		'is_virtuoso_member' => 'bool'
	];

	protected $dates = [
		'password_reset_token_expiry',
		'impersonation_token_expiry'
	];

	protected $hidden = [
		'password',
		'password_reset_token',
		'impersonation_token',
		'google_authentication_token'
	];

	protected $fillable = [
		'name',
		'email',
		'password',
		'first_name',
		'last_name',
		'profile_image_url',
		'phone',
		'location',
		'iata_number',
		'agency_name',
		'job_title',
		'preferred_timezone_tzab',
		'password_reset_token',
		'password_reset_token_expiry',
		'account_status_id',
		'friendly_identifier',
		'default_currency_id',
		'impersonation_token',
		'impersonation_token_expiry',
		'google_authentication_token',
		'linkedin_url',
		'agency_usage_mode_id',
		'address_line1',
		'address_line2',
		'state',
		'zip',
		'city',
		'country_id',
		'is_virtuoso_member',
		'preferred_partner_id',
        'hourly_rate',
	];

	public function account_status()
	{
		return $this->belongsTo(AccountStatus::class);
	}

	public function agency_usage_mode()
	{
		return $this->belongsTo(AgencyUsageMode::class);
	}

	public function country()
	{
		return $this->belongsTo(Country::class);
	}

	public function currency_type()
	{
		return $this->belongsTo(CurrencyType::class, 'default_currency_id');
	}

	public function advisor_assigned_copilots()
	{
		return $this->hasMany(AdvisorAssignedCopilot::class, 'copilot_id');
	}

	public function advisor_chats()
	{
		return $this->hasMany(AdvisorChat::class, 'sender_id');
	}

	public function advisor_requests()
	{
		return $this->hasMany(AdvisorRequest::class, 'created_by_id');
	}

	public function booking_ocrs()
	{
		return $this->hasMany(BookingOcr::class, 'created_by_id');
	}

	public function client_info()
	{
		return $this->hasOne(ClientInfo::class);
	}

	public function copilot_duties()
	{
		return $this->hasMany(CopilotDuty::class, 'copilot_id');
	}

	public function copilot_info()
	{
		return $this->hasOne(CopilotInfo::class, 'copilot_id');
	}

	public function default_itinerary_theme()
	{
		return $this->hasOne(DefaultItineraryTheme::class);
	}

	public function itineraries()
	{
		return $this->hasMany(Itinerary::class);
	}

	public function master_accounts()
	{
		return $this->hasMany(MasterAccount::class, 'created_by_id');
	}

	public function master_account()
	{
		return $this->hasOne(MasterAccount::class);
	}

	public function master_sub_accounts()
	{
		return $this->hasMany(MasterSubAccount::class, 'created_by_id');
	}

	public function master_sub_account()
	{
		return $this->hasOne(MasterSubAccount::class);
	}

	public function saved_suppliers()
	{
		return $this->hasMany(SavedSupplier::class);
	}

	public function service_suppliers()
	{
		return $this->hasMany(ServiceSupplier::class, 'created_by_id');
	}

	public function stripe_audit_logs()
	{
		return $this->hasMany(StripeAuditLog::class);
	}

	public function stripe_checkout_logs()
	{
		return $this->hasMany(StripeCheckoutLog::class);
	}

	public function stripe_connect_reminders()
	{
		return $this->hasMany(StripeConnectReminder::class);
	}

	public function stripe_payment_intents()
	{
		return $this->hasMany(StripePaymentIntent::class);
	}

	public function stripe_subscription_histories()
	{
		return $this->hasMany(StripeSubscriptionHistory::class);
	}

	public function travellers()
	{
		return $this->hasMany(Traveller::class, 'created_by_id');
	}

	public function user_notes()
	{
		return $this->hasMany(UserNote::class, 'created_by_id');
	}

	public function roles()
	{
		return $this->belongsToMany(Role::class, 'user_role')
					->withPivot('id', 'stripe_subscription', 'has_valid_license')
					->withTimestamps();
	}
	public function user_sessions()
	{
		return $this->hasMany(UserSession::class);
	}

	public function testimonials()
	{
		return $this->hasMany(Testimonials::class);
	}

	public function user_stripe_account()
	{
		return $this->hasOne(UserStripeAccount::class);
	}

	public function partner()
	{
		return $this->belongsTo(PreferredPartners::class, 'preferred_partner_id');
	}
	public function copilot_average_feedback()
	{
		return $this->belongsTo(CopilotAverageFeedback::class, 'copilot_id');
	}



}
