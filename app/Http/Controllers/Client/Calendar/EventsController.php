<?php

namespace App\Http\Controllers\Client\Calendar;

use App\Http\Controllers\Client\MyItinerariesQueryTrait;
use App\Http\Controllers\Controller;
use App\ModelsExtended\BookingCategory;
use App\ModelsExtended\Interfaces\IBookingModelInterface;
use App\ModelsExtended\Itinerary;
use App\ModelsExtended\ModelBase;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class EventsController extends Controller
{
    use MyItinerariesQueryTrait;

    public function index( )
    {
        $this->validatedRules(
            [
                'from' => 'required|date_format:Y-m-d',
                'to' => 'required|date_format:Y-m-d|after_or_equal:from',
            ]
        );

        $from = Carbon::createFromFormat("Y-m-d", request("from"))->setTime(0,0);
        $to = Carbon::createFromFormat("Y-m-d", request("to"))->setTime(0,0);

        $query =  $this->myItineraries();
//            ->where( function ( Builder  $builder  ){
//                $builder->where(function (Builder $builder) {
//                    $builder->where("start_date", ">=", request("from"))
//                        ->where("start_date", "<=", request("to"));
//                })
//                    ->orWhere(function (Builder $builder) {
//                        $builder->where("end_date", ">=", request("from"))
//                            ->where("end_date", "<=", request("to"));
//                    });
//            });

        $r = $query->get()->map(function(Itinerary $itinerary) use ($from, $to) {
            return $itinerary->getAllBookingsOnItinerary()
                ->filter(function (IBookingModelInterface $bookingModel) use ($itinerary){
                    return collect([
                        BookingCategory::Flight,
                        BookingCategory::Hotel,
                        BookingCategory::Concierge,
                        BookingCategory::Cruise,
                        BookingCategory::Transportation,
                        BookingCategory::Tour_Activity,
                        BookingCategory::Insurance,
                        BookingCategory::Other_Notes,
                    ])->contains($bookingModel->booking_category_id);
                })
                ->filter(function (IBookingModelInterface $bookingModel) use ($from, $to){
                    $start_date = $bookingModel->calendarStartDate()->setTime(0,0);
                    $end_date = $bookingModel->calendarEndDate()->setTime(0,0);

                    return  ( $start_date->greaterThanOrEqualTo($from) && $to->greaterThanOrEqualTo($start_date) )
                         ||
                        ( $end_date->greaterThanOrEqualTo($from) && $to->greaterThanOrEqualTo($end_date) );
                })
                ->map(function (IBookingModelInterface $bookingModel) use ($itinerary){
                return [
                    "uniqueId" => $bookingModel->id . "-" . $itinerary->id,
                    "category_id" => $bookingModel->booking_category_id,
                    "booking_id" => $bookingModel->id,
                    "itinerary_id" => $itinerary->id,
                    "title" => $bookingModel->title(),
                    "itinerary" => $itinerary->title(),
                    "start" => $bookingModel->calendarStartDate()->toIso8601String(),
                    "end" => $bookingModel->calendarEndDate()->toIso8601String(),
                    "allDay" => true,
                    "resource" => null,
                   // "details" => $bookingModel->formatForSharing(),
                ];
            });

        } );

        return $r->flatten(1);
    }

    public function detail(Request $request)
    {
        $this->validatedRules(
            [
                'itinerary_id' => 'required|numeric|exists:itinerary,id',
                'booking_id' => 'required|numeric',
                'category_id' => 'required|exists:booking_category,id',
            ]
        );

        $booking = ModelBase::getBookingByCategoryId(
            $request->input('itinerary_id'),
            $request->input('category_id'),
            $request->input('booking_id')
        );

        // if I do not own this, throw exception
        if (!$booking->itinerary->traveller->traveller_emails->pluck("email")->contains(auth()->user()->email ) )
            throw new \Exception("You do not have access to view this detail!");

        return $booking->formatForSharing();
    }
}
