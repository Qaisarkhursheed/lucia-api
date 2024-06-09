<?php

namespace App\Http\Controllers\Agent\Itinerary\Bookings;

use App\Http\Controllers\Controller;
use App\Http\Responses\OkResponse;
use App\ModelsExtended\BookingCategory;
use App\ModelsExtended\Interfaces\IBookingModelInterface;
use App\ModelsExtended\Interfaces\IShareableSortableInterface;
use App\ModelsExtended\ItineraryConcierge;
use App\ModelsExtended\ItineraryCruise;
use App\ModelsExtended\ItineraryDivider;
use App\ModelsExtended\ItineraryFlight;
use App\ModelsExtended\ItineraryHeader;
use App\ModelsExtended\ItineraryHotel;
use App\ModelsExtended\ItineraryInsurance;
use App\ModelsExtended\ItineraryOther;
use App\ModelsExtended\ItineraryTour;
use App\ModelsExtended\ItineraryTransport;
use App\ModelsExtended\ModelBase;
use App\ModelsExtended\Traits\ReplicableEloquentTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingsController extends Controller
{
    /**
     * @return int|object|string|null
     */
    protected function getItineraryId()
    {
        return \request()->route('itinerary_id');
    }

    /**
     * Allows sorting of bookings
     * @param Request $request
     * @return OkResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function shiftBooking(Request $request)
    {
        $this->validatedRules(
            [
                'shift_date' => 'required|date_format:Y-m-d',
                'shift' => 'required|array|min:1',
                'shift.*.sorting_rank' => 'required|numeric|min:0|max:3000',
                'shift.*.booking_id' => 'required|numeric',
                'shift.*.category_id' => 'required|exists:booking_category,id',
            ]
        );

        DB::transaction(function () use ($request) {
            foreach ($request->input('shift') as $shift) {
                $booking = $this->getBooking($shift['category_id'], $shift['booking_id']);
                $booking->moveStartDate(Carbon::createFromFormat('Y-m-d', $request->input('shift_date')));
                $booking->sorting_rank = $shift['sorting_rank'];
                $booking->updateQuietly();
            }
        });


        return new OkResponse();
    }

    /**
     * @return array
     * @throws Exception
     */
    public function duplicate(): array
    {
        $this->validatedRules(
            [
                'booking_id' => 'required|numeric',
                'category_id' => 'required|exists:booking_category,id',
            ]
        );

        return $this->getBooking(\request('category_id'), \request('booking_id'))
            ->duplicateWithRelations()
            ->saveWithRelations()
            ->formatForSharing();
    }

    /**
     * @param int $category_id
     * @param int $booking_id
     * @return Builder|Model|IBookingModelInterface|ReplicableEloquentTrait|IShareableSortableInterface
     * @throws Exception
     */
    private function getBooking(int $category_id, int $booking_id)
    {
        return  ModelBase::getBookingByCategoryId( $this->getItineraryId(), $category_id, $booking_id );
    }
}
