<?php

namespace Database\Seeders;

use Database\Factories\ModelsExtended\UserFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class UsersTableSeeder extends Seeder
{
    const profile_image_url = "default/profile_picture.png";

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->storeDefaultProfilePics()
            ->count(3)
            ->create();
    }

    /**
     * @return UserFactory
     */
    public function storeDefaultProfilePics()
    {
        $file = file_get_contents( "https://scadware.gabustenderlovingcareacademy.org/api/?p=/resources/persons/profile-pictures/fetch/sm&ID=0&FileName=" );

        Storage::cloud()->put( self::profile_image_url , $file );

        return new UserFactory();
    }

    public static function getDefaultProfileImageURl()
    {
        return Storage::cloud()->url( self::profile_image_url );
    }

}
