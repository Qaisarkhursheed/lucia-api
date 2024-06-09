<?php

namespace App\ModelsExtended;

use App\ModelsExtended\Interfaces\IBookingModelInterface;
use App\ModelsExtended\Interfaces\ICalenderRenderCompatibleInterface;
use App\ModelsExtended\Interfaces\IDeveloperPresentationInterface;
use App\ModelsExtended\Interfaces\IGlobalSearchableInterface;
use App\ModelsExtended\Interfaces\IShareableCategorizedInterface;
use App\ModelsExtended\Interfaces\IShareableRenderInterface;
use App\ModelsExtended\Interfaces\IShareableSortableInterface;
use App\ModelsExtended\Interfaces\ITuiCalenderRenderCompatibleInterface;
use App\ModelsExtended\Interfaces\ShareableCategorizedFunctions;
use App\ModelsExtended\Traits\CanCreateGoogleCalendarEventTrait;
use App\ModelsExtended\Traits\ShareableSortablePackagerTrait;
use App\Repositories\Calendars\GoogleCalendars\GoogleCalendarClient;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 *
 * @property Collection|ItineraryConcierge[] $itinerary_concierges
 * @property Collection|ItineraryCruise[] $itinerary_cruises
 * @property Collection|ItineraryDivider[] $itinerary_dividers
 * @property Collection|ItineraryDocument[] $itinerary_documents
 * @property Collection|ItineraryFlight[] $itinerary_flights
 * @property Collection|ItineraryHeader[] $itinerary_headers
 * @property Collection|ItineraryHotel[] $itinerary_hotels
 * @property Collection|ItineraryInsurance[] $itinerary_insurances
 * @property Collection|ItineraryOther[] $itinerary_others
 * @property Collection|ItineraryPassenger[] $itinerary_passengers
 * @property Collection|ItineraryPicture[] $itinerary_pictures
 * @property Collection|ItineraryTask[] $itinerary_tasks
 * @property ItineraryTheme $itinerary_theme
 * @property Collection|ItineraryTour[] $itinerary_tours
 * @property Collection|ItineraryTransport[] $itinerary_transports
 *
 * @property User $user
 * @property Traveller $traveller
 */
class Itinerary extends \App\Models\Itinerary
    implements ICalenderRenderCompatibleInterface, IShareableRenderInterface,
    ITuiCalenderRenderCompatibleInterface, IGlobalSearchableInterface, IDeveloperPresentationInterface

    // Disable Itinerary from creating calendar for now
    //, ICanCreateGoogleCalendarEventInterface
{
    use HasFactory, CanCreateGoogleCalendarEventTrait;

     public $doNotReplicateProperties = [
         'share_itinerary_key',
         'google_calendar_event_id',
     ];

     public array $replicableRelations = [
        'itinerary_concierges',
        'itinerary_cruises',
        'itinerary_documents',
        'itinerary_flights',
        'itinerary_hotels',
        'itinerary_insurances',
        'itinerary_others',
        'itinerary_passengers',
        'itinerary_pictures',
        'itinerary_theme',
        'itinerary_tours',
        'itinerary_transports',
        'itinerary_dividers',
        'itinerary_headers',
     ];

    protected $appends = [
        'identification'
    ];

    /**
     * @param int $id
     * @return Itinerary|null
     */
    public static function getById(int $id): ?Itinerary
    {
        return self::find($id);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function itinerary_dividers()
    {
        return $this->hasMany(ItineraryDivider::class);
    }

    public function itinerary_headers()
    {
        return $this->hasMany(ItineraryHeader::class);
    }

    public function itinerary_hotels()
    {
        return $this->hasMany(ItineraryHotel::class);
    }

    public function advisor_requests()
    {
        return $this->hasMany(AdvisorRequest::class);
    }

    public function itinerary_flights()
    {
        return $this->hasMany(ItineraryFlight::class);
    }

    public function itinerary_cruises()
    {
        return $this->hasMany(ItineraryCruise::class);
    }

    public function itinerary_concierges()
    {
        return $this->hasMany(ItineraryConcierge::class);
    }

    public function itinerary_insurances()
    {
        return $this->hasMany(ItineraryInsurance::class);
    }

    public function itinerary_passengers()
    {
        return $this->hasMany(ItineraryPassenger::class);
    }

    public function itinerary_pictures()
    {
        return $this->hasMany(ItineraryPicture::class);
    }

    public function itinerary_tasks()
    {
        return $this->hasMany(ItineraryTask::class);
    }

    public function itinerary_tours()
    {
        return $this->hasMany(ItineraryTour::class);
    }

    public function itinerary_transports()
    {
        return $this->hasMany(ItineraryTransport::class);
    }

    public function itinerary_others()
    {
        return $this->hasMany(ItineraryOther::class);
    }

    public function itinerary_documents()
    {
        return $this->hasMany(ItineraryDocument::class);
    }

    public function traveller()
    {
        return $this->belongsTo(Traveller::class);
    }

    public function itinerary_theme()
    {
        return $this->hasOne(ItineraryTheme::class);
    }

    /**
     * Create Identification from the id
     * @return string
     */
    public function getIdentificationAttribute()
    {
        return (string)Str::of( strval( $this->id ) )->padLeft( 4, '0' );
    }

    /**
     * Get traceable folder path
     */
    public function getFolderStorageRelativePath(): string
    {
        return sprintf(
            "%s/itineraries/%s",
            $this->user->friendly_identifier,
            $this->getIdentificationAttribute()
        );
    }

    /**
     * @param bool $forceRecreate
     * @return $this
     */
    public function generateShareKey( bool $forceRecreate = false): Itinerary
    {
        if( $forceRecreate || ! $this->share_itinerary_key )
            $this->updateQuietly([
                "share_itinerary_key" => trim( base64_encode( $this->id  . '-' . Carbon::now()->timestamp ) , '=' )
            ]);

        return $this;
    }

    /**
     * Gets the preview URL for the app. It will regenerate it if not available
     *
     * @return string
     */
    public function getAppPreviewURL(): string
    {
        $this->generateShareKey();
        return Str::of( env( "UI_APP_URL" )  )->finish( '/' ) . 'public/itinerary/' . $this->share_itinerary_key;
    }

    /**
     * Gets the preview URL for the api. It will regenerate it if not available
     *
     * @return string
     */
    public function getApiPreviewURL(): string
    {
        $this->generateShareKey();
        return Str::of( env( "APP_URL" )  )->finish( '/' ) . 'shares/itineraries/' . $this->share_itinerary_key;
    }

    public function formatForCalendar()
    {
        return [
            "id" => $this->id,
            "title" => $this->title(),
            "start" => $this->start_date,
            "end" => $this->end_date,
            "allDay" => true,
            "resource" => null,
        ];
    }

    /**
     * @inheritDoc
     */
    public function title(): ?string
    {
        return $this->title;
    }

    public function notes(): ?string
    {
        return $this->abstract_note;
    }

    /**
     * @return \Illuminate\Support\Collection|IBookingModelInterface[]|ShareableSortablePackagerTrait[]
     */
    public function getAllBookingsOnItinerary()
    {
        $items = collect();
        return $items->merge( $this->itinerary_flights )
                    ->merge( $this->itinerary_hotels )
                    ->merge( $this->itinerary_cruises )
                    ->merge( $this->itinerary_transports )
                    ->merge( $this->itinerary_concierges )
                    ->merge( $this->itinerary_tours )
                    ->merge( $this->itinerary_insurances )
                    ->merge( $this->itinerary_dividers )
                    ->merge( $this->itinerary_headers )
                 ->merge( $this->itinerary_others );
    }

    /**
     * This is supposed to get the next sorting rank for on this day
     *
     * @param Carbon $day
     * @return int
     */
    public function getNextSortingRankFor(Carbon $day): int
    {
        // pick only bookings that match the sortKey $day
        // now pick the latest of them according to when it was created
        // get its sorting_rank and add 10 to it
        $lastBookingOfTheDay = $this->getAllBookingsOnItinerary()
            ->filter(fn( IBookingModelInterface $sortable) => $sortable->sortByKey()->isSameDay( $day ))
            ->sortByDesc( fn( IBookingModelInterface $x ) => $x->created_at->timestamp )
            ->first();

        return $lastBookingOfTheDay? $lastBookingOfTheDay->sorting_rank + 10 : 1;
    }

    /**
     * @inheritDoc
     */
    public function formatForSharing():array
    {

        $bookings = $this->getAllBookingsOnItinerary();

        return [
            "id" => $this->id,

            'identification' => $this->getIdentificationAttribute(),
            'title' => $this->title(),
            "client" => $this->traveller->presentForDev(),
//            "client_details" => $this->traveller->presentForDev(),
//            "client_phone" => $this->traveller->phone,
//            "client_emails" => $this->traveller->traveller_emails->pluck('email'),
//            "support_documents" => $this->traveller->traveller_documents->map->presentForDev(),

            'start_date' => $this->start_date->format( "F, d Y" ),
            'end_date' => $this->end_date->format( "F, d Y" ),

            'pictures' => $this->itinerary_pictures->pluck( 'image_url' ),
            'documents' => $this->itinerary_documents->map->presentForDev(),

            'sharing' => $this->getSharing(),

            'status' => $this->itinerary_status->description,
            'abstract_note' => $this->abstract_note,
            'show_price_on_share' => $this->show_price_on_share,
            'mark_as_client_approved' => $this->mark_as_client_approved,
            'currency' => $this->currency_type->description,
            'currency_id' => $this->currency_id,
            'total_price' => $this->show_price_on_share ? $this->total_price : null,

            "travelers" => $this->itinerary_passengers->map( function (ItineraryPassenger $item ){
                return  [
                    "name" => $item->name,
                    "passenger_type" => $item->passenger_type->description
                ];
            } ),

            "itinerary_theme" => optional($this->itinerary_theme)->presentForDev(),

            "bookings" => $bookings
                ->sort(function ( IShareableSortableInterface $a, IShareableSortableInterface $b  ){
//                    if( $c === 0 && $a instanceof ItineraryFlight )
//                        return -1; // flight should come before anything on same day.
//
//                    if( $c === 0 && $a instanceof ItineraryHotel )
//                        return 1; // hotel should after anything on same day.

                    return ShareableCategorizedFunctions::compareTo( $a , $b );
                    })->values()
//                ->sortByDesc(function ( $item, $index ){ return $item->sortByKey()->timestamp;})->values()
                ->map->packageAsSortableShareable(),

            "dates" => $this->getPackagedDates()
        ];
    }

    /**
     * @return array
     */
    public function packForFetch(): array
    {
        $formattedData = $this->formatForSharing();

        $collect_final = collect([]);
        $grouped = collect($formattedData["bookings"])->groupBy("sortable_date");
        foreach ($formattedData["dates"] as $date) {
            $collect_final->put($date, $grouped->has($date) ? $grouped->get($date) : []);
        }

        // Added orphans to last date
        $orphaned = $grouped->except($formattedData["dates"]);
        if ($orphaned->count()) {
            $collect_final->put($collect_final->keys()->last(),
                array_merge( Arr::last( $collect_final->toArray() ), $orphaned->flatten(1)->toArray())
            );
        }

        $formattedData["bookings"] = $collect_final->toArray();
        $formattedData["requests"] = $this->advisor_requests->map->only([ "id", "request_title", "advisor_request_status_id" ]);
        $formattedData["tasks"] = $this->itinerary_tasks->map->presentForDev();

        return $formattedData;
    }

    /**
     * @inheritDoc
     */
    public function formatTuiArray()
    {
        $bgColor = \Faker\Factory::create()->hexColor() ;
        return [
            "id" => $this->id,
            "title" => $this->title(),
            "start" => $this->start_date,
            "end" => $this->end_date,
            "allDay" => true,
            "resource" => null,

            "isAllday" => true,
            "calendarId" => $this->id,

            "category" => "allday",
            "dueDateClass" => "",
            "color" =>"#ffffff" ,
            "bgColor" =>  $bgColor,
            "dragBgColor" => $bgColor,
            "borderColor" => $bgColor,

            "isFocused" => false,
            "isPending" => false,
            "isVisible" => true,
            "isReadOnly" => true,

            "state" => "Free",

            "isPrivate" => false,
            "location" => $this->user->location,
            "attendees" => $this->itinerary_passengers->pluck('name'),

            "body" => "<hr/><h6>Client Information</h6>".
                $this->traveller->name . " <br/>".
                sprintf( "<b><i class='fa fa-envelope'></i></b> <a href='mailto:%s'>%s</a>  <br/>", optional( $this->traveller->traveller_emails->first() )->email, optional( $this->traveller->traveller_emails->first() )->email  ).
                sprintf( "<b><i class='fa fa-phone'></i></b> <a href='tel:%s'>%s</a> <br/>" , $this->traveller->phone, $this->traveller->phone ) .
                sprintf( "<hr/><h6>AGENT | %s</h6>", strtoupper( $this->user->agency_name ) ).
                $this->user->name . " <br/>".
                sprintf( "<b><i class='fa fa-envelope'></i></b> <a href='mailto:%s'>%s</a>  <br/>", $this->user->email, $this->user->email).
                sprintf( "<b><i class='fa fa-phone'></i></b> <a href='tel:%s'>%s</a> <br/>" , $this->user->phone, $this->user->phone )
        ];
    }

    /**
     * @inheritDoc
     */
    public function globalSearchResultView(): array
    {
        return array_merge(
            Arr::only( $this->formatForCalendar(), [ "id", "title","start","end"] ),
            [
                'status' => $this->itinerary_status->description,
                "client" => $this->traveller->name,
            ]
        );
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    private function getPackagedDates()
    {
        $c = collect();
        for ( $i = $this->start_date->clone(); $i->lessThanOrEqualTo( $this->end_date ); $i->addDay() )
            $c->push ( $i->format( IShareableSortableInterface::SORT_DATE_FORMAT ) );

        return $c;
    }

    /**
     * @inheritDoc
     */
    public function createCalendarEvent(bool $updateQuietly = true)
    {
        try {

            $client = new GoogleCalendarClient( User::getCalendarSyncUser() );
            if( $this->status_id == ItineraryStatus::Accepted && $client->getCanConnect() )
            {
                $event_id = $this->google_calendar_event_id;
                if( $event_id )
                    $event_id = $client->updateEvent( $event_id,
                        $this->title(), $this->start_date, $this->end_date,
                        $this->traveller->defaultEmail->email,
                        $this->notes()
                    );
                else
                    $event_id = $client->createEvent($this->title(),
                        $this->start_date, $this->end_date, $this->traveller->defaultEmail->email, $this->notes()
                    );

                $this->google_calendar_event_id = $event_id;

                if( $updateQuietly ) $this->updateQuietly();
            }
        }catch (\Exception $exception)
        {
            Log::error( "error creating itinerary on google calendar. " . $exception->getMessage(), $exception->getTrace() );
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function presentForDev(): array
    {
        return $this->formatForSharing();
    }

    /**
     * @return array
     */
    public function getSharing(): array
    {
        if( !$this->share_itinerary_key ) $this->generateShareKey();

        return [
            "key" => $this->share_itinerary_key,
            "apiUrl" => $this->getApiPreviewUrl(),
            "appUrl" => $this->getAppPreviewURL()
        ];
    }
}
