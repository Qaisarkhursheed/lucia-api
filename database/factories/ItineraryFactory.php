<?php

namespace Database\Factories;

use App\ModelsExtended\CurrencyType;
use App\ModelsExtended\Itinerary;
use App\ModelsExtended\ItineraryStatus;
use App\ModelsExtended\Role;
use App\ModelsExtended\Traveller;
use App\ModelsExtended\User;
use Carbon\Carbon;
use Database\Factories\Itinerary\TravellerFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

class ItineraryFactory extends Factory
{
    protected $model = Itinerary::class;

    protected Carbon $start_date;
    protected Carbon $end_date;
    protected float $price;
    protected string $payment;
    protected string $confirmation_reference;
    protected string $cancel_policy;
    protected string $notes;

    public function __construct(...$params)
    {
        parent::__construct(...$params);

        $this->start_date = Carbon::now()->addDays( rand(0, 10) );
        $this->end_date = $this->start_date->clone()->addDays(rand(5, 15));
        $this->price = $this->faker->randomFloat( 2, 500, 5000000);
        $this->payment  = $this->faker->randomElement( [ 'Cash' , 'Bank' ] );
        $this->confirmation_reference = $this->faker->swiftBicNumber();
        $this->cancel_policy = $this->faker->sentence();
        $this->notes = $this->faker->sentence(9);
    }

    public function definition(): array
    {
        // create client
        $user_id = $this->getUserId();
        $traveller = $this->createOrUpdateTraveller($user_id);

        return [
            'user_id' =>$user_id,
            'title' => $this->faker->country() . ' ' . Carbon::now()->addYears( rand( 1 , 20) )->year,
            'abstract_note' => $this->faker->sentence( 50 ),
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status_id' => Arr::random( ItineraryStatus::all()->toArray() ) [ "id" ],
            'show_price_on_share' => Arr::random( [ false, true ] ),
            "traveller_id" => $traveller->id ,
            'currency_id' => CurrencyType::USD,
        ];
    }

    /**
     * @return int
     */
    private function getUserId()
    {
        return Arr::random(
            User::query()->where("role_id", Role::Agent)
                ->get()
                ->toArray()
        ) ["id"];
    }

    /**
     * @param int $user_id
     * @return Traveller|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     */
    public static function createOrUpdateTraveller( int $user_id)
    {
        $travellerFactory = (object)( new TravellerFactory(  ) )->makeOne()->toArray();
        return Traveller::createOrUpdateTravellerUsing(
            $travellerFactory->name, $travellerFactory->phone, $user_id, $travellerFactory->abstract_note
        );
    }
}
