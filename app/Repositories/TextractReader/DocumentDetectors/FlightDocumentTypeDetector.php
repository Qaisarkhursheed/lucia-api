<?php

namespace App\Repositories\TextractReader\DocumentDetectors;

use App\ModelsExtended\BookingOcr;
use App\Repositories\TextractReader\DocumentReaders\DocumentReaderAbstract;
use App\Repositories\TextractReader\DocumentReaders\Flights\Flight0001Reader;
use App\Repositories\TextractReader\DocumentReaders\Flights\Flight0002Reader;
use App\Repositories\TextractReader\DocumentReaders\Flights\Flight0003Reader;
use App\Repositories\TextractReader\DocumentReaders\Flights\Flight0004Reader;
use App\Repositories\TextractReader\DocumentReaders\Flights\Flight0005Reader;
use App\Repositories\TextractReader\DocumentReaders\Flights\Flight0006Reader;
use App\Repositories\TextractReader\DocumentReaders\Flights\Flight0007Reader;
use App\Repositories\TextractReader\DocumentTypeDetector;
use App\Repositories\TextractReader\Exceptions\UnrecognizedDocumentTypeException;
use App\Repositories\TextractReader\IDocumentReader;

class FlightDocumentTypeDetector extends DocumentTypeDetector
{
    /**
     * @var array | IDocumentReader[]
     */
    private array $flightDocumentTypes;

    public function __construct()
    {
        $this->flightDocumentTypes = [
           Flight0001Reader::class,
           Flight0002Reader::class,
           Flight0003Reader::class,
           Flight0004Reader::class,
           Flight0005Reader::class,
           Flight0006Reader::class,
           Flight0007Reader::class,
        ];
    }

    /**
     * @param BookingOcr $ocr
     * @return IDocumentReader
     * @throws UnrecognizedDocumentTypeException
     */
    public function getDocumentReader(BookingOcr $ocr): IDocumentReader
    {
        foreach ( $this->flightDocumentTypes as $readerClassName )
        {
            $reader = DocumentReaderAbstract::resolveReader(  $readerClassName, $ocr );
            if( $reader->canRead($ocr->completed_ocr_recognition_log->api_response) ) return $reader;
        }

        throw new UnrecognizedDocumentTypeException("flights" );
    }
}
