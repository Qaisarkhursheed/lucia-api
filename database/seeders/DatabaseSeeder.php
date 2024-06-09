<?php

namespace Database\Seeders;

use App\Models\AdvisorRequestType;
use App\Models\Airport;
use App\Models\Country;
use App\ModelsExtended\AccountStatus;
use App\ModelsExtended\Airline;
use App\ModelsExtended\ApplicationProduct;
use App\ModelsExtended\Role;
use App\ModelsExtended\User;
use Database\Factories\UserFactory;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        if( ! Airport::query()->exists() )
            $this->call([
                AirportsSeeder::class,
            ]);

        if( ! Airline::query()->exists() )
            $this->call([
                AirlinesSeeder::class,
            ]);

        if( ! Country::query()->exists() )
            $this->call([
                CountriesSeeder::class,
            ]);

        if( ! AdvisorRequestType::query()->exists() )
            $this->call([
                AdvisorRequestTypeSeeder::class,
            ]);

        $this->call([
            AccountStatusSeeder::class,
            AdvisorRequestStatusSeeder::class,
            AgencyUsageModeSeeder::class,
            AmenitySeeder::class,
            BeddingTypeSeeder::class,
            BookingCategorySeeder::class,
            ChatContentTypeSeeder::class,
            CurrencyTypeSeeder::class,
            FeedbackTopicSeeder::class,
            ItineraryStatusSeeder::class,
            PassengerTypeSeeder::class,
            PrioritySeeder::class,
            TransitTypeSeeder::class,
            PropertyPositionSeeder::class,
            PropertyDesignSeeder::class,
            RoleSeeder::class,
            OcrStatusSeeder::class,
        ]);

        $this->createOrUpdateMainAdmin();

        $this->createAppProducts();

//        if( app()->environment() === 'development' )
//        {
//            $this->call([
//                UsersTableSeeder::class,
//            ]);
//        }

    }

    /**
     * Update or create default administrator
     */
    private function createOrUpdateMainAdmin()
    {
        $user = User::getDefaultAdmin();
        if( $user )
        {
            $user->update([
                "email" => env( 'APP_ADMIN_EMAIL' ),
                "account_status_id" => AccountStatus::APPROVED
            ]);

            $user->roles()->updateOrCreate([
                "user_id" => $user->id,
                "role_id" => Role::Administrator,
            ]);
        }
        else{
            $factory = new UserFactory(1);
            $user = $factory->create([
                "id" => User::DEFAULT_ADMIN,
                "email" => env( 'APP_ADMIN_EMAIL' ),
                "account_status_id" => AccountStatus::APPROVED
            ]);
            $user->roles()->updateOrCreate([
                "user_id" => $user->id,
                "role_id" => Role::Administrator,
            ]);
        }
    }

    private function createAppProducts()
    {
//        if( ! ApplicationProduct::query()->exists() )
            $this->call([ ApplicationProductSeeder::class ]);
    }
}
