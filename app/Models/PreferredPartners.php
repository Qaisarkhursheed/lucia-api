<?php

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Illuminate\Database\Eloquent\Model;

class PreferredPartners extends ModelBase
{
    protected $table = 'preferred_partners';
	public $timestamps = false;


	protected $fillable = [
		'company_name',
		'contact_person_name',
		'monthly_price',
		'annual_price',
		'contact_email',
		'website',
		'logo'
	];
}
