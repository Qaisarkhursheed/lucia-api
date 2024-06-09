<?php

namespace Database\Factories\Itinerary\Bookings\Flights;

use App\ModelsExtended\FlightPicture;
use Database\Factories\Itinerary\ItineraryPictureFactory;

class FlightPictureFactory extends ItineraryPictureFactory
{
    protected $model = FlightPicture::class;

    const image_url1 = "default/itinerary/flights/plane1.png";
    const image_url2 = "default/itinerary/flights/plane2.png";
    const image_url3 = "default/itinerary/flights/plane3.png";
    const image_url4 = "default/itinerary/flights/plane4.png";
    const image_url5 = "default/itinerary/flights/plane5.png";

    const image_url1_src = "https://www.specialevents.com/sites/specialevents.com/files/styles/article_featured_retina/public/Venue_01_Air_Hollywood_2.jpg?itok=ccsm3l-z";
    const image_url2_src = "https://i.ytimg.com/vi/PJoXvbnAbmA/maxresdefault.jpg";
    const image_url3_src = "https://ichef.bbci.co.uk/news/976/cpsprodpb/4EC5/production/_118656102_5e1882c9-ba79-49f9-b4b9-7a361b7f305d.jpg";
    const image_url4_src = "https://mk0runwaygirl0t0gjwt.kinstacdn.com/wp-content/uploads/2020/06/A380-Air-France-scaled.jpg";
    const image_url5_src = "https://i0.wp.com/www.financialafrik.com/wp-content/uploads/2018/12/turkish-airlines.jpg?fit=1168%2C606&ssl=1";

}
