<?php

namespace App\ModelsExtended;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\ModelsExtended\ModelBase;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\ModelsExtended\Interfaces\IHasImageUrlInterface;
use App\ModelsExtended\Traits\HasImageUrlSavingModelTrait;
use App\ModelsExtended\Interfaces\IDeveloperPresentationInterface;
use App\ModelsExtended\Interfaces\IHasFolderStoragePathModelInterface;

class PreferredPartners extends \App\Models\PreferredPartners implements IHasImageUrlInterface, IDeveloperPresentationInterface, IHasFolderStoragePathModelInterface
{
	use HasImageUrlSavingModelTrait;
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

	public static function savePartnerLogo($request){

		return self::saveImageOnCloud( $request->file('logo'), new self);
		
	}


	 /**
     * Call setFriendlyIdentifier() first if new
     * get the expected profile picture relative path
     * @return string
     */
    public static function getProfilePictureRelativePath()
    {
        // to help with cache bursting
        return  sprintf('/partner_logo-%s.png', Str::random());
    }

    /**
     * Get traceable folder path
     */
    public function getFolderStorageRelativePath(): string
    {
        return  (string)Str::of( strval( $this->id ) )->padLeft( 4, '0' );
    }

	public function presentForDev(): array
	{
		return $this->asArray();
	}
}
