<?php

namespace App\Repositories\TextractReader\DocumentDetectors;

use App\ModelsExtended\BookingOcr;
use App\Repositories\TextractReader\DocumentReaders\DocumentReaderAbstract;
use App\Repositories\TextractReader\DocumentReaders\Hotels\Hotel0001Reader;
use App\Repositories\TextractReader\DocumentReaders\Hotels\Hotel0002Reader;
use App\Repositories\TextractReader\DocumentReaders\Hotels\Hotel0003Reader;
use App\Repositories\TextractReader\DocumentReaders\Hotels\Hotel0004Reader;
use App\Repositories\TextractReader\DocumentReaders\Hotels\Hotel0005Reader;
use App\Repositories\TextractReader\DocumentReaders\Hotels\Hotel0006Reader;
use App\Repositories\TextractReader\DocumentReaders\Hotels\Hotel0007Reader;
use App\Repositories\TextractReader\DocumentTypeDetector;
use App\Repositories\TextractReader\Exceptions\UnrecognizedDocumentTypeException;
use App\Repositories\TextractReader\IDocumentReader;

class HotelDocumentTypeDetector extends DocumentTypeDetector
{
    /**
     * @var array | IDocumentReader[]
     */
    private array $hotelDocumentTypes;

    public function __construct()
    {
        $this->hotelDocumentTypes = [
            Hotel0001Reader::class,
            Hotel0002Reader::class,
            Hotel0003Reader::class,
            Hotel0004Reader::class,
            Hotel0005Reader::class,
            Hotel0006Reader::class,
            Hotel0007Reader::class,
        ];
    }

    /**
     * @param BookingOcr $ocr
     * @return IDocumentReader
     * @throws UnrecognizedDocumentTypeException
     */
    public function getDocumentReader(BookingOcr $ocr): IDocumentReader
    {
        foreach ( $this->hotelDocumentTypes as $readerClassName )
        {
            $reader = DocumentReaderAbstract::resolveReader(  $readerClassName, $ocr );
            if( $reader->canRead($ocr->completed_ocr_recognition_log->api_response) ) return $reader;
        }

        throw new UnrecognizedDocumentTypeException("hotels" );
    }
}
