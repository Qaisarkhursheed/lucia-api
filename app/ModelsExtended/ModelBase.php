<?php

namespace App\ModelsExtended;

use App\ModelsExtended\Interfaces\IBookingModelInterface;
use App\ModelsExtended\Interfaces\IHasImageUrlInterface;
use App\ModelsExtended\Interfaces\IReplicableEloquent;
use App\ModelsExtended\Traits\ReplicableEloquentTrait;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @method getFolderStorageRelativePath():string
 */
class ModelBase extends Model implements IReplicableEloquent
{
    use ReplicableEloquentTrait;

    public $doNotReplicateProperties = [
        'google_calendar_event_id'
    ];

    public function updateWithRelation( array $data, $relations = [])
    {
        $this->update( $data );
        foreach ( $relations as $relation )
        {
            // eager load if not loaded
            if( !  $this->relationLoaded( $relation ) ) $this->load( $relation );
            // update
            $this->getRelation( $relation )->update( $data );
        }
    }

    /**
     * Get traceable folder path
     * @param string $image_url
     * @return string
     */
    public static function getStorageRelativePath( string $image_url ): string
    {
        return ltrim( parse_url( $image_url, PHP_URL_PATH ), '/' );
    }

    /**
     * Get traceable folder path if you implement a property **image_url** [ IHasImageUrlInterface ]
     */
    public function getImageUrlStorageRelativePath(): ?string
    {
        if( $this->image_url ) return self::getStorageRelativePath( $this->image_url );
        return null;
    }

    /**
     * @param int $itinerary_id
     * @param int $category_id
     * @param int $booking_id
     * @return Builder|Model|IBookingModelInterface
     * @throws Exception
     */
    public static function getBookingByCategoryId(int $itinerary_id, int $category_id, int $booking_id)
    {
        switch ($category_id) {
            case BookingCategory::Flight:
                return ItineraryFlight::query()
                    ->where("itinerary_id", $itinerary_id)
                    ->where("id", $booking_id)
                    ->firstOrFail();
            case BookingCategory::Hotel:
                return ItineraryHotel::query()
                    ->where("itinerary_id", $itinerary_id)
                    ->where("id", $booking_id)
                    ->firstOrFail();
            case BookingCategory::Concierge:
                return ItineraryConcierge::query()
                    ->where("itinerary_id", $itinerary_id)
                    ->where("id", $booking_id)
                    ->firstOrFail();
            case BookingCategory::Cruise:
                return ItineraryCruise::query()
                    ->where("itinerary_id", $itinerary_id)
                    ->where("id", $booking_id)
                    ->firstOrFail();
            case BookingCategory::Transportation:
                return ItineraryTransport::query()
                    ->where("itinerary_id", $itinerary_id)
                    ->where("id", $booking_id)
                    ->firstOrFail();
            case BookingCategory::Tour_Activity:
                return ItineraryTour::query()
                    ->where("itinerary_id", $itinerary_id)
                    ->where("id", $booking_id)
                    ->firstOrFail();
            case BookingCategory::Insurance:
                return ItineraryInsurance::query()
                    ->where("itinerary_id", $itinerary_id)
                    ->where("id", $booking_id)
                    ->firstOrFail();
            case BookingCategory::Other_Notes:
                return ItineraryOther::query()
                    ->where("itinerary_id", $itinerary_id)
                    ->where("id", $booking_id)
                    ->firstOrFail();

            case BookingCategory::Divider:
                return ItineraryDivider::query()
                    ->where("itinerary_id", $itinerary_id)
                    ->where("id", $booking_id)
                    ->firstOrFail();

            case BookingCategory::Header:
                return ItineraryHeader::query()
                    ->where("itinerary_id", $itinerary_id)
                    ->where("id", $booking_id)
                    ->firstOrFail();
        }
        throw new Exception("Invalid Booking Category Specified!");
    }
}
