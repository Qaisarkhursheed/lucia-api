<?php

namespace App\Http\Controllers\Constants;

use App\Models\DbTimezone;
use App\ModelsExtended\Role;
use App\Models\AccountStatus;
use App\ModelsExtended\Airline;
use App\ModelsExtended\Airport;
use App\ModelsExtended\Amenity;
use App\ModelsExtended\Country;
use App\ModelsExtended\Priority;
use App\Models\AdvisorRequestType;
use App\ModelsExtended\BeddingType;
use App\ModelsExtended\TransitType;
use App\Http\Controllers\Controller;
use App\ModelsExtended\CurrencyType;
use App\ModelsExtended\FeedbackTopic;
use App\ModelsExtended\PassengerType;
use App\ModelsExtended\PropertyDesign;
use App\ModelsExtended\AgencyUsageMode;
use App\ModelsExtended\BookingCategory;
use App\ModelsExtended\ItineraryStatus;
use App\ModelsExtended\PropertyPosition;
use App\ModelsExtended\ApplicationProductPrice;
use App\ModelsExtended\PreferredPartners;
use App\Repositories\Stripe\StripeSubscriptionSDK;
use Illuminate\Http\Request;

class ConstantController extends Controller
{
    public function accountStatus()
    {
        return AccountStatus::orderBy("id")->get();
    }

    public function avatars()
    {
        $urls = [];
        foreach (glob(public_path('avatar-icons') . "/*") as $filename) {
            $urls[] = myAssetUrl( 'avatar-icons/' . basename($filename));
        }

        return $urls;
    }

    public function advisorRequestType()
    {
        return AdvisorRequestType::query()
            ->where("is_active", true)
            ->orderBy("id")
            ->get();
    }

    public function agencyUsageMode()
    {
        return AgencyUsageMode::orderBy("id")->get();
    }

    public function airports()
    {
        return Airport::orderBy("name")
            ->where("active", true)
            ->select(
            "name", "iata", "countryCode"
        )->get();
    }

    public function airlines()
    {
        return Airline::orderBy("name")
            ->select(
                "name", "iata", "icao"
            )->get();
    }

    public function amenities()
    {
        return Amenity::all();
    }

    public function beddingTypes()
    {
        return BeddingType::query()->where("is_active", true )
                ->orderBy("sort_order")->get();
    }

    public function bookingCategory()
    {
        return BookingCategory::orderBy("id")->get();
    }

    public function countries()
    {
        return Country::with("timezone")
            ->orderBy("countries.description")
            ->get()->map->presentForDev();
    }

    public function currencyTypes()
    {
        return CurrencyType::orderBy("id")->get();
    }

    public function feedbackTopics()
    {
        return FeedbackTopic::orderBy("id")->get();
    }

    public function itineraryStatus()
    {
        return ItineraryStatus::orderBy("id")->get();
    }

    public function passengerType()
    {
        return PassengerType::orderBy("id")->get();
    }

    public function priority()
    {
        return Priority::orderBy("id")->get();
    }

    public function roles()
    {
        return Role::orderBy("id")->get();
    }

    public function timezones()
    {
        return DbTimezone::query()->select("timezone_id", "offset_tzab", "offset_gmt" )
            ->orderBy("timezone_id")->get();
    }

    public function transitType()
    {
        return TransitType::orderBy("id")->get();
    }

    public function propertyPosition()
    {
        return PropertyPosition::orderBy("id")->get();
    }

    public function propertyDesign()
    {
        return PropertyDesign::orderBy("id")->get();
    }

    public function subscriptionPrices()
    {
        return ApplicationProductPrice::query()
//            ->whereNotIn( "id", [ \App\ModelsExtended\ApplicationProductPrice::LUCIA_COPILOT_ONLY_MONTHLY ])
            ->get()->map(fn( ApplicationProductPrice $x) => $x->presentForDev());
    }

    public function partnerSubscriptionPrices(Request $request){
        
        $PreferredPartners = PreferredPartners::find($request->partner_id);
        $sdk = new StripeSubscriptionSDK();
        $result = array();
        if($PreferredPartners){
            if($PreferredPartners->monthly_price):
                $result['Monthly'] =  $sdk->getPriceById($PreferredPartners->monthly_price);
            endif;
            if($PreferredPartners->annual_price):
                $result['Yearly'] =  $sdk->getPriceById($PreferredPartners->annual_price);
            endif;
        }
       
        return $result;
    }

}
