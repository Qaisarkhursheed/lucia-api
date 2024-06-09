<?php

namespace App\Repositories\TextractReader;

use App\ModelsExtended\BookingOcr;
use App\Repositories\TextractReader\DocumentDetectors\FlightDocumentTypeDetector;
use App\Repositories\TextractReader\DocumentDetectors\HotelDocumentTypeDetector;
use App\Repositories\TextractReader\Exceptions\UnrecognizedDocumentTypeCategoryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DocumentTypeDetector
{
    private const NO_MATCH_KEYWORD = "--------NO--MATCH";

    /**
     * @param int $totalOccurrenceCount
     * @param int $count
     * @return float|int
     */
    private static function calculateThresholdPercentage(int $totalOccurrenceCount, int $count)
    {
        return ($totalOccurrenceCount/$count) * 100;
    }

    /**
     * Fetches a reader for the document
     *
     * @param array $jsonArray
     * @return IDocumentReader
     * @throws UnrecognizedDocumentTypeCategoryException
     */
    public function detect(BookingOcr $ocr ): IDocumentReader
    {
        if( $this->isFlightDocument( $ocr->completed_ocr_recognition_log->api_response ) )
            return (new FlightDocumentTypeDetector())->getDocumentReader( $ocr );

        if( $this->isHotelDocument( $ocr->completed_ocr_recognition_log->api_response ) )
            return (new HotelDocumentTypeDetector())->getDocumentReader( $ocr );

        throw new UnrecognizedDocumentTypeCategoryException();
    }

    /**
     * @param array $jsonArray
     * @return bool
     */
    private function isFlightDocument(array $jsonArray):bool
    {
        //Flight
        // -- Contains Ignore case, like Word%
        // -- Flight, Depart, Reservation Code, Airline, Arrival, Terminal
        return $this->passesThresholdOnLines($jsonArray,
            [
                'Flight', 'Depart', 'Reservation Code', 'Confirmation', 'Airline',
                'Arrival', 'Arrive', 'Terminal', 'Seat', "Air", "Class", "Travelers", "Segments"
            ],
            50
        );
    }

    /**
     * @param array $jsonArray
     * @return bool
     */
    private function isHotelDocument(array $jsonArray):bool
    {
        //Hotel
        //  -- Contains Ignore case, like Word%
        // -- Guest, Room, Reservation, Night, Dinner, Breakfast
        return $this->passesThresholdOnLines($jsonArray,
            [ 'Guest', 'Room', 'Reservation', 'Night', 'Dinner', 'Breakfast' ],
            60
        );
    }

    /**
     * percent match in line contains
     *
     * @param array $jsonArray
     * @param array $keyWords
     * @param float $threshold
     * @return bool
     */
    public static function passesThresholdOnLines(array $jsonArray, array $keyWords, float $threshold = 70): bool
    {
        $keyWords = self::getMatchableKeyWords($keyWords);
        $totalOccurrenceCount = self::getUniqueKeyWordMatchCount($jsonArray['Lines'], $keyWords);
        $thresholdRate = self::calculateThresholdPercentage( $totalOccurrenceCount, count($keyWords) );
//        dd( $thresholdRate );
        return $thresholdRate >= $threshold;
    }

    /**
     * @param array $jsonArray
     * @return IDocumentReader
     * @throws UnrecognizedDocumentTypeCategoryException
     */
    private function getHotelDocumentReader(array $jsonArray):IDocumentReader
    {
        throw new UnrecognizedDocumentTypeCategoryException();
    }

    /**
     * @param string $sentence
     * @param array $keyWords
     * @return string
     */
    public static function getMatchedKeyWordsAsString(string $sentence, array $keyWords): string
    {
        return collect( $keyWords )->filter(function ( string $item ) use( $sentence, $keyWords ){
           return Str::of( $sentence )->contains( $item );
        })->implode(",") ;
    }

    /**
     * @param array $lines
     * @param array $keyWords
     * @return int
     */
    private function getKeyWordMatchCount(array $lines, array $keyWords): int
    {
        // you can log matches to view
        return $this->getKeyWordMatches( $lines, $keyWords)->sum(fn(int $val) => $val);
    }

    /**
     * This only returns the counts of the keyword itself
     *  <= $keywords length
     *
     * @param array $lines
     * @param array $keyWords
     * @return int
     */
    public static function getUniqueKeyWordMatchCount(array $lines, array $keyWords): int
    {
        $matches = self::getKeyWordMatches( $lines, $keyWords)->keys()->toArray();
//        dd($matches);
        return collect($matches)
            ->map(fn(string $keyword) => explode(",", $keyword ) )
            ->flatten()->unique()->count();

        // BAD Algorithm
//        return collect( $keyWords )
//            ->sum(function (string $item) use($matches){
//                return Str::of($item )->contains( $matches ) ? 1 : 0;
//            });
    }

    /**
     * Get Keywords where the lines contain the words
     *
     * @param array $lines
     * @param array $keyWords
     * @return Collection
     */
    public static function getKeyWordMatches(array $lines, array $keyWords): Collection
    {
        // you can log matches to view

//        if we don't care about the items that match
//        $totalOccurrenceCount = collect( $jsonArray['Lines'] )
//            ->countBy( function (string $value, int $key) use ($keyWords){
//            return Str::of( strtolower($value) )->contains( $keyWords )? "match" : "no-match";
//        })["match"];
        return collect($lines)
            ->countBy(function (string $value, int $key) use ($keyWords) {

                return Str::of(strtolower($value))->contains($keyWords) ?
                    self::getMatchedKeyWordsAsString(strtolower($value), $keyWords)
                    : self::NO_MATCH_KEYWORD;
            })
            ->filter(fn(int $val, string $key) => $key != self::NO_MATCH_KEYWORD);
    }

    /**
     * @param array $keywords
     * @return array
     */
    public static function getMatchableKeyWords( array $keywords): array
    {
        return collect($keywords)
            ->map(fn( string $keyword ) => strtolower( $keyword ))
            ->toArray();
    }

    /**
     * Get Keywords that starts a line.It will be unique to keywords
     * because one keyword can only start a line
     *
     * @param array $lines
     * @param array $keyWords
     * @return Collection
     */
    public static function getKeyWordStartsLines(array $lines, array $keyWords): Collection
    {
        // we can use preg_match here
        // you can log matches to view
        return collect($lines)
            ->countBy(function (string $value, int $key) use ($keyWords) {
                foreach ($keyWords as $keyWord)
                {
                    if( Str::of(strtolower($value))->startsWith($keyWord) )
                        return $keyWord;
                }
                return self::NO_MATCH_KEYWORD;
            })
            ->filter(fn(int $val, string $key) => $key != self::NO_MATCH_KEYWORD);
    }

    /**
     * percent match
     *
     * @param array $jsonArray
     * @param array $keyWords
     * @param float $threshold
     * @return bool
     */
    public static function passesThresholdOnLinesStartingWith(array $jsonArray, array $keyWords, float $threshold = 70): bool
    {
        $keyWords = self::getMatchableKeyWords($keyWords);
        $keywordMatched = self::getKeyWordStartsLines($jsonArray['Lines'], $keyWords);
//        Log::info( __CLASS__ , [$keyWords, $keywordMatched]);
        return self::calculateThresholdPercentage( count($keywordMatched), count($keyWords) ) >= $threshold;
    }

}
