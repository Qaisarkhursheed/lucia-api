<?php

namespace Database\Factories\Itinerary;

use App\ModelsExtended\ItineraryPicture;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class ItineraryPictureFactory extends Factory
{
    const image_url1 = "default/itinerary/sea-fall.png";
    const image_url2 = "default/itinerary/ship-yards.png";
    const image_url3 = "default/itinerary/beach.png";
    const image_url4 = "default/itinerary/long-ships.png";
    const image_url5 = "default/itinerary/nature-sea.png";

    const image_url1_src = "https://hips.hearstapps.com/hbu.h-cdn.co/assets/17/33/isle-of-skye.jpg?crop=1xw:1.0xh;center,top&resize=980:*";
    const image_url2_src = "https://hips.hearstapps.com/hbu.h-cdn.co/assets/17/33/mykonos.jpg?crop=1xw:1.0xh;center,top&resize=980:*";
    const image_url3_src = "https://hips.hearstapps.com/hbu.h-cdn.co/assets/17/33/mallorca-spain.jpg?crop=1xw:1.0xh;center,top&resize=980:*";
    const image_url4_src = "https://hips.hearstapps.com/hbu.h-cdn.co/assets/17/33/st-barts-france.jpg?crop=1xw:1.0xh;center,top&resize=980:*";
    const image_url5_src = "https://hips.hearstapps.com/hbu.h-cdn.co/assets/17/33/whitsunday-islands.jpg?crop=1xw:1.0xh;center,top&resize=980:*";


    protected $model = ItineraryPicture::class;

    public function definition(): array
    {
        // push file to cloud
        return [
            'image_url' => static::getImageURl( Arr::random([
                static::image_url1,
                static::image_url2,
                static::image_url3,
                static::image_url4,
                static::image_url5,
            ])
            ),
        ];
    }

    /**
     * Store necessary files
     * @return UserFactory
     */
    public static function storeDefaultPics()
    {
        if( ! Storage::cloud()->exists( static::image_url1  ) )
        Storage::cloud()->put( static::image_url1 ,
            file_get_contents( static::image_url1_src )
        );

        if( ! Storage::cloud()->exists( static::image_url2  ) )
        Storage::cloud()->put( static::image_url2 ,
            file_get_contents( static::image_url2_src )
        );

        if( ! Storage::cloud()->exists( static::image_url3  ) )
        Storage::cloud()->put( static::image_url3 ,
            file_get_contents( static::image_url3_src )
        );

        if( ! Storage::cloud()->exists( static::image_url4  ) )
        Storage::cloud()->put( static::image_url4 ,
            file_get_contents( static::image_url4_src )
        );

        if( ! Storage::cloud()->exists( static::image_url5  ) )
        Storage::cloud()->put( static::image_url5 ,
            file_get_contents( static::image_url5_src )
        );

        return new UserFactory();
    }

    /**
     * @param string $image_relative_url
     * @return string
     */
    public static function getImageURl( string $image_relative_url )
    {
        return Storage::cloud()->url( $image_relative_url );
    }
}
