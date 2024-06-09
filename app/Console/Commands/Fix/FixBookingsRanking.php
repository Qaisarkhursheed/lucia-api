<?php

namespace App\Console\Commands\Fix;

use App\ModelsExtended\Interfaces\IShareableSortableInterface;
use App\ModelsExtended\Itinerary;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class FixBookingsRanking extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:bookings-ranking {itinerary_id?} {--all : run for all itineraries}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will update booking ranks on itineraries';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // careful, make sure user pass --all as argument to run all
        // else specify itinerary id because it will override all manual sorting done
        if( !$this->option('all') && !$this->argument('itinerary_id') )
        {
            $this->info( 'You must use all option or parse in an itinerary id' );
            return false;
        }

        $this->withProgressBar( $this->getItineraries() , function ( Itinerary $itinerary ){
            $this->fixBookingRank( $itinerary );
        });
        $this->info( "\n-------------------------------------" );
        $this->info( "COMPLETED" );

        return true;
    }

    /**
     * @return Collection|Itinerary[]
     */
    private function getItineraries()
    {
        return Itinerary::query()
            ->whereNull('deleted_at')
            ->when(!$this->option('all') && $this->argument('itinerary_id') , function ( Builder  $builder ){
                $builder->where( "id", $this->argument('itinerary_id') );
            })
            ->get();
    }

    /**
     * @param Itinerary $itinerary
     * @return void
     */
    private function fixBookingRank(Itinerary $itinerary)
    {
        // pick  bookings and group them by their sortDay
        // order each group by the latest of them according to when it was created
        // get its sorting_rank and add 10 to it
        $itinerary->getAllBookingsOnItinerary()
            ->groupBy(fn( IShareableSortableInterface $sortable) => $sortable->sortByKey()->toDateString())
            ->each( fn( Collection $x ) => $x->sort(fn( IShareableSortableInterface $x ) => $x->created_at->timestamp )
                ->each(function( IShareableSortableInterface $x, int $index ){ $x->sorting_rank = $index + 1; $x->updateQuietly(); })
            );
    }
}
