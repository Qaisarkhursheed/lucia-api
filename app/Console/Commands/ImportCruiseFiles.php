<?php

namespace App\Console\Commands;

use App\Models\ServiceShip;
use App\Models\ShipPort;
use App\ModelsExtended\BookingCategory;
use App\ModelsExtended\ServiceSupplier;
use App\ModelsExtended\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\SplFileInfo;

class ImportCruiseFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:cruise-files {folder-path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will import cruise information from folder path specified';

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
        $folder = $this->argument('folder-path');
        $this->info( "let's begin working on " . $folder );
        $this->info( ""  );

        $this->info( "-------------------------------------" );
        $this->info( "PROCESS SEARCH XML FILES" );
        $this->info( "-------------------------------------" );
        $this->processFiles( File::files( $folder ) );

        $this->info( "-------------------------------------" );
        $this->info( "PROCESS CRUISE CONTENT XML FILES" );
        $this->info( "-------------------------------------" );
        $this->processFolders( File::directories( $folder ) );


        $this->info( "-------------------------------------" );
        $this->info( "COMPLETED" );

        return true;
    }

    /**
     * @param array | SplFileInfo[] $allFiles
     */
    private function processFiles(array $allFiles)
    {
        foreach ( $allFiles as $file )
        {
            $this->processSimpleSearchXmlFile( $file );
        }
    }

    private function processSimpleSearchXmlFile(SplFileInfo $file)
    {
        $this->info( "processing " . $file->getRealPath() );

        $results = $this->getXMLContentAsObject( $file )[ 'results' ];

        // Check if file is empty
        if( ! array_key_exists( 'cruise', $results ) ) return;

        $cruises = $this->expectsIndexedMultiDimensionalArray(
            $this->getXMLContentAsObject( $file )[ 'results' ][ 'cruise' ]
        );

        foreach ( $cruises as $cruise ){
            DB::transaction( function () use ( $cruise ){
                $cruiseAttributes = $cruise['line'][ '@attributes' ];
                $supplier = $this->createOrUpdateSupplier(
                    $cruiseAttributes[ 'name' ],
                    null,
                    $cruiseAttributes[ 'id' ],
                    $cruiseAttributes[ 'logourl' ]
                );

                // Create Ship from the attributes
                $shipAttributes = $cruise['ship'][ '@attributes' ];
                $ship = $this->createOrUpdateShip(  $shipAttributes[ 'name' ], $supplier->id, $shipAttributes[ 'id' ]);

                // Create Ports
                foreach ( $this->expectsIndexedMultiDimensionalArray( $cruise[ 'ports' ]['port'] ) as $item )
                {
                    $attributes = $item[ '@attributes' ];
                    $this->createOrUpdatePort(
                        $attributes['name'], $supplier->id,
                        intval( $attributes['id'] )
                    );
                }
            } );
        }
    }

    /**
     * @param SplFileInfo $file
     * @return mixed
     */
    private function getXMLContentAsObject( SplFileInfo $file)
    {
        $xmlObject = simplexml_load_string($file->getContents());
        return json_decode( json_encode($xmlObject), true);
    }

    /**
     * @param array | string[] $directories
     */
    private function processFolders(array $directories)
    {
        foreach ( $directories as $directory )
        {
            foreach ( File::allFiles( $directory ) as $file )
            {
                $this->processCruiseContentXmlFile( $file );
            }
        }
    }

    /**
     * @param SplFileInfo $file
     */
    private function processCruiseContentXmlFile(SplFileInfo $file)
    {
        $cruise = $this->getXMLContentAsObject( $file )[ 'results' ][ 'cruise' ];
        $cruiseAttributes = $cruise[ '@attributes' ];
        $this->info( "processing " . $file->getRealPath() );

        DB::transaction( function () use ( $cruise , $cruiseAttributes){
            $supplier = $this->createOrUpdateSupplier(
                $cruiseAttributes[ 'cruiseline' ],
                $cruiseAttributes[ 'description' ],
                $cruiseAttributes[ 'lineid' ],
                null
            );

            // Create Ship from the attributes
            $this->createOrUpdateShip(  $cruiseAttributes[ 'shipname' ], $supplier->id, $cruiseAttributes[ 'shipid' ]);

            // Create Ship from sailings tag if it exists
            if( array_key_exists( 'sailings', $cruise ) )
                foreach ( $this->expectsIndexedMultiDimensionalArray ( $cruise[ 'sailings' ]['sailing'] ) as $sailing )
                {
                    $this->createOrUpdateShip(  $sailing[ '@attributes' ][ 'shipname' ] , $supplier->id, $sailing[ '@attributes' ][ 'shipid' ]);
                }

            // Create Ports
            foreach ( $this->expectsIndexedMultiDimensionalArray( $cruise[ 'itinerary' ]['item'] ) as $item )
            {
                $attributes = $item[ '@attributes' ];
                if( $attributes['type'] != 'port' ) continue;

                $this->createOrUpdatePort(
                    $attributes['name'], $supplier->id,
                    intval( $attributes['portid'] ),
                     $attributes['description'],
                    Arr::get($attributes, 'latitude'),
                     Arr::get($attributes, 'longitude')
                );
            }
        } );
    }

    /**
     * @param string $name
     * @param string|null $description
     * @param null|int $ref_id
     * @param null|string $image_url
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|ServiceSupplier
     */
    private function createOrUpdateSupplier(string $name, string $description = null, $ref_id = null, $image_url = null)
    {
        $supplier = ServiceSupplier::getSupplier( $name,  BookingCategory::Cruise );
        if( $supplier )
        {
            if( $description ) $supplier->description = $description;
            if( $image_url ) $supplier->image_url = $image_url;
            if( $ref_id ) $supplier->ref_id = $ref_id;

            $supplier->update();
            return $supplier;
        }

        return ServiceSupplier::create([
            'name' => $name,
            'description' => $description,
            'ref_id' => $ref_id,
            'image_url' => $image_url,
            'booking_category_id' => BookingCategory::Cruise,
            'created_by_id' => User::DEFAULT_ADMIN,
            'is_globally_accessible' => true
        ]);
    }

    /**
     * @param string $name
     * @param int $service_supplier_id
     * @param null|int $ref_ship_id
     * @return ServiceShip|Builder|\Illuminate\Database\Eloquent\Model|object
     */
    private function createOrUpdateShip(string $name, int $service_supplier_id, $ref_ship_id = null  )
    {

        $ship = $this->searchShip( $name, $service_supplier_id, $ref_ship_id );
        if( $ship )
        {
            $ship->update([
                'name' => $name,
                'ref_ship_id' => $ref_ship_id,
                'service_supplier_id' => $service_supplier_id
            ]);
            return $ship;
        }

        return ServiceShip::create(
            [
                'name' => $name,
                'ref_ship_id' => $ref_ship_id,
                'service_supplier_id' =>$service_supplier_id
            ]);
    }

    /**
     * Creates or updates ship port for supplier
     * @param string $name
     * @param int $service_supplier_id
     * @param null|int $ref_port_id
     * @param null|string $description
     * @param null|string $latitude
     * @param null|string $longitude
     * @return ShipPort|Builder|\Illuminate\Database\Eloquent\Model|object|ShipPort
     */
    private function createOrUpdatePort(string $name, int $service_supplier_id, $ref_port_id = null, $description = null, $latitude = null, $longitude = null )
    {
        $ship = $this->searchPort( $name, $service_supplier_id, $ref_port_id );
        if( $ship )
        {
            if( $description ) $ship->description = $description;
            if( $latitude ) $ship->latitude = $latitude;
            if( $longitude ) $ship->longitude = $longitude;
            $ship->update([
                'name' => $name,
                'ref_port_id' => $ref_port_id
            ]);
            return $ship;
        }

        return ShipPort::create(
            [
                'ref_port_id' => $ref_port_id,
                'description' => $description,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'name' => $name,
                'service_supplier_id' =>$service_supplier_id
            ]);
    }

    /**
     * @param string $name
     * @param int $service_supplier_id
     * @param null $ref_ship_id
     * @return Builder|\Illuminate\Database\Eloquent\Model|object|null|ServiceShip
     */
    private function searchShip( string $name, int $service_supplier_id, $ref_ship_id = null)
    {
        return ServiceShip::query()
            ->where( function ( Builder $query ) use ( $service_supplier_id , $name ){
                $query->where( "service_supplier_id", $service_supplier_id )
                    ->where( "name", $name );
            })
            ->orWhere( function ( Builder $query ) use ( $service_supplier_id , $ref_ship_id ){
                $query->where( "service_supplier_id", $service_supplier_id )
                    ->where( "ref_ship_id", $ref_ship_id );
            })
            ->first();
    }

    /**
     * @param string $name
     * @param int $service_supplier_id
     * @param null $ref_port_id
     * @return Builder|\Illuminate\Database\Eloquent\Model|object|null|ShipPort
     */
    private function searchPort( string $name, int $service_supplier_id, $ref_port_id = null)
    {
        return ShipPort::query()
            ->where( function ( Builder $query ) use ( $service_supplier_id , $name ){
                $query->where( "service_supplier_id", $service_supplier_id )
                    ->where( "name", $name );
            })
            ->orWhere( function ( Builder $query ) use ( $service_supplier_id , $ref_port_id ){
                $query->where( "service_supplier_id", $service_supplier_id )
                    ->where( "ref_port_id", $ref_port_id );
            })
            ->first();
    }

    /**
     * @param array $arr
     * @return array|array[]
     */
    private function expectsIndexedMultiDimensionalArray( array $arr )
    {
        return isMultiDimensionalArray( $arr ) && Arr::first( array_keys( $arr ) ) === 0 ? $arr : [ $arr ];
    }

}
