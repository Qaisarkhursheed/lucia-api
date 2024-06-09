<?php

namespace App\Http\Controllers\Agent\Itinerary;

use App\ModelsExtended\Itinerary;
use App\ModelsExtended\ItineraryPicture;
use App\ModelsExtended\ModelBase;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PictureController extends ItineraryItemsController implements IBookingCanStoreImageControllerInterface
{
    protected int $MAXIMUM_PICTURES = 1;

    public function __construct()
    {
        parent::__construct( "picture_id" );
    }

    public function getDataQuery(): Builder
    {
        return  $this->limitBookingToItineraryOwner( ItineraryPicture::query())
            ->where( "itinerary_id", $this->getItineraryId() );
    }

    public function getCommonRules()
    {
        return [
            'image_url' => 'required|image|max:20000',    // 20MB
        ];
    }

    /**
     * @inheritDoc
     */
    public function store( Request $request )
    {
        return $this->invokeImageStoreRequest( $request, $this, $this->getItinerary(), 'itinerary_pictures');
    }

    /**
     * @param UploadedFile $image
     * @param ModelBase|Itinerary $baseModel
     * @return mixed
     */
    public function storeImage(UploadedFile $image, ModelBase $baseModel)
    {
        // delete old pictures but not from AWS
        $baseModel->itinerary_pictures()->delete();

        $image_url =  ItineraryPicture::generateImageRelativePath( $image,  $baseModel );

        Storage::cloud()->put( $image_url, $image->getContent() );

        return ItineraryPicture::create(
            [
                'itinerary_id' => $this->getItineraryId(),
                'image_url' => Storage::cloud()->url( $image_url )
            ]
        );
    }

}
