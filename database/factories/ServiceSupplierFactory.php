<?php

namespace Database\Factories;

use App\ModelsExtended\BookingCategory;
use App\ModelsExtended\ServiceSupplier;
use App\ModelsExtended\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

class ServiceSupplierFactory extends Factory
{
    protected $model = ServiceSupplier::class;

    public function definition(): array
    {
        $bookingCategoryId = Arr::random( BookingCategory::all()->toArray() ) [ "id" ];

        return [
            'name' => $this->getFakeUniqueName( $bookingCategoryId ),
            'address' => $this->faker->address,
            'phone' => $this->faker->phoneNumber,
            'website' => $this->faker->url,
            'email' => $this->faker->email,
            'booking_category_id' => $bookingCategoryId,
            'created_by_id' => User::DEFAULT_ADMIN,
            'is_globally_accessible' => true
        ];
    }

    /**
     * Creates one that doesn't exist in database
     * @return string
     */
    private function getFakeUniqueName( int $bookingCategoryId )
    {
        do{
            $val = $this->faker->name;
        }while ( ServiceSupplier::where( "name", $val )
                    ->where( "booking_category_id", $bookingCategoryId )->first() );

        return $val;
    }
}
