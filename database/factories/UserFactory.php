<?php

namespace Database\Factories;

use App\Models\AccountStatus;
use App\ModelsExtended\Role;
use App\ModelsExtended\User;
use Database\Seeders\UsersTableSeeder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

/**
 *
 */
class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return (
            new User( [
            'email' => $this->faker->unique()->safeEmail,
            'password' => app('hash')->make('123456'),
            'first_name'  => $this->faker->firstName,
            'last_name'  => $this->faker->lastName,
            'phone'  => $this->faker->phoneNumber,
            'location'  => $this->faker->address,
            'job_title'  => $this->faker->jobTitle,
            'agency_name'  => $this->faker->company,
            'profile_image_url'  => UsersTableSeeder::getDefaultProfileImageURl(),
            'account_status_id'  => Arr::random( AccountStatus::all()->toArray() ) [ "id" ],
        ]
        )
        )->setFriendlyIdentifier()
         ->makeVisible( 'password' )
         ->toArray();
    }


}
