<?php

namespace App\ModelsExtended;

use App\Models\ViewLatestClientEmail;
use App\ModelsExtended\Interfaces\IDeveloperPresentationInterface;
use App\ModelsExtended\Interfaces\IGlobalSearchableInterface;
use App\ModelsExtended\Interfaces\IHasFolderStoragePathModelInterface;
use App\ModelsExtended\Traits\HasImageUrlSavingModelTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

/**
 * @property string|null $image_url
 * @property ViewLatestClientEmail | null $defaultEmail
 * @property Collection|Itinerary[] $itineraries
 * @property Collection|TravellerDocument[] $traveller_documents
 */
class Traveller extends \App\Models\Traveller implements IHasFolderStoragePathModelInterface, IDeveloperPresentationInterface, IGlobalSearchableInterface
{
    protected $appends = [ 'image_url'  ];

    use HasImageUrlSavingModelTrait;

    /**
     * This is just to support legacy approach
     *
     * @return string|null
     */
    public function getImageUrlStorageRelativePath(): ?string
    {
        return $this->image_relative_url;
    }

    public function itineraries()
    {
        return $this->hasMany(Itinerary::class);
    }

    public function traveller_documents()
    {
        return $this->hasMany(TravellerDocument::class);
    }

    /**
     * @param int $id
     * @return Traveller
     */
    public static function getById(int $id): Traveller
    {
        return self::find($id);
    }

    /**
     * @param string $name
     * @param int $created_by_id
     * @return Traveller|Builder|\Illuminate\Database\Eloquent\Model|object
     */
    public static function getByName(string $name, int $created_by_id)
    {
        return self::query()
            ->where( 'name' , $name)
            ->where( 'created_by_id' , $created_by_id)
            ->first();
    }

    /**
     * Get traceable folder path
     */
    public function getFolderStorageRelativePath(): string
    {
        return sprintf(
            "travellers/%s",
            $this->id
        );
    }

    /**
     * @param string $name
     * @param string|null $phone
     * @param string|null $abstract_note
     * @param Carbon|null $birthday
     * @return Builder|\Illuminate\Database\Eloquent\Model|Traveller
     */
  public static function createOrUpdateTraveller (string $name, ?string $phone, ?string $abstract_note = null,
                                                  ?Carbon $birthday = null
  )
  {
      return self::createOrUpdateTravellerUsing( $name, $phone, auth()->id(), $abstract_note, $birthday);
  }

    /**
     * @param string $name
     * @param string|null $phone
     * @param int $created_by_id
     * @param string|null $abstract_note
     * @param Carbon|null $birthday
     * @return Builder|\Illuminate\Database\Eloquent\Model|Traveller
     */
    public static function createOrUpdateTravellerUsing (string $name, ?string $phone, int $created_by_id,
                                                         ?string $abstract_note = null,
                                                         ?Carbon $birthday = null )
    {
        $traveller = self::getByName( $name , $created_by_id );
        if( $traveller )
        {
            $traveller->birthday = $birthday?? $traveller->birthday;
            $traveller->abstract_note = $abstract_note?? $traveller->abstract_note;
            $traveller->phone = $phone?? $traveller->phone;
            $traveller->update();
            return  $traveller;
        }
        return Traveller::query()->create(
            [
                'name' => $name,
                'created_by_id' => $created_by_id,
                'birthday' => $birthday,
                'abstract_note' => $abstract_note,
                'phone' => $phone,
            ]
        );
    }

    public function defaultEmail()
    {
        return $this->hasOne(ViewLatestClientEmail::class, 'itinerary_client_id');
    }

    /**
     * This doesn't call update method.
     *
     * @param UploadedFile|null $file
     * @return $this
     */
    public function setImage(?UploadedFile $file): Traveller
    {
        if( $file  )
        {
            $this->image_relative_url = Traveller::generateImageRelativePath($file, $this );
            Storage::cloud()->put( $this->image_relative_url, $file->getContent() );
        }

        return $this;
    }
    public function presentForDev(): array
    {
       return [
           'id'=> $this->id,
           'name' => $this->name,
           'phone'=> $this->phone,
           'abstract_note'=> $this->abstract_note,
           'birthday'=> $this->birthday,
           'address'=> $this->address,
           'image_url'=> $this->image_url,
           "emails" => $this->traveller_emails->pluck('email'),
           "support_documents" => $this->traveller_documents->map->presentForDev(),
       ];
    }

    /**
     * @inheritDoc
     */
    public function globalSearchResultView(): array
    {
        return array_merge( Arr::except( $this->presentForDev(), [ "support_documents" ] ), [ "email" => optional($this->defaultEmail)->email ]);
    }
}
