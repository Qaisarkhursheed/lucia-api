<?php

namespace App\Repositories\TextractReader\DocumentReaders;

use App\ModelsExtended\BookingOcr;
use App\ModelsExtended\CurrencyType;
use App\ModelsExtended\Itinerary;
use App\Repositories\TextractReader\IDocumentReader;
use Carbon\Carbon;
use Illuminate\Support\ItemNotFoundException;
use Illuminate\Support\Str;

abstract class DocumentReaderAbstract
{
    const INDEX_NOT_FOUND = -1;

    /**
     * like 8/13/2022
     */
    const DATE_FORMAT__M__D__YYYY = "n/j/Y";

    const DATE_FORMAT__MM__DD__YYYY = "m/d/Y";

    /**
     * 30/07/2022
     */
    const DATE_FORMAT__DD__MM__YYYY = "d/m/Y";


    /**
     * like 05 JUL 2022
     */
    const DATE_FORMAT__DD__MMM__YYYY = "d M Y";

    /**
     * like 05JUL2022
     */
    const DATE_FORMAT__DDMMMYYYY = "dMY";

    /**
     * like 2022 JUL 05
     */
    const DATE_FORMAT__YYYY__MMM__DD = "Y M d";

    /**
     * like 05 JUL 22
     */
    const DATE_FORMAT__DD__MMM__YY = "d M y";

    /**
     * like Friday 15th March 2013
     */
    const DATE_FORMAT__DDD_DDT__MMMM__YYYY = "l jS F Y";

    /**
     * like August 28th 2022
     */
    const DATE_FORMAT__MMMM__DDT__YYYY = "F jS Y";

    /**
     * NB: month here is trailing zero m but date is with no trailing!
     * like 8/13/2022
     */
    const DATE_FORMAT__D__M__YYYY = "j/m/Y";


    const HR_12_REGEX = "((1[0-2]|0?[1-9]):([0-5][0-9])([AaPp][Mm]))";

    /**
     * @var array|string[]
     */
    protected array $lines;

    /**
     * @var array|array[]
     */
    protected array $tables;

    /**
     * @var array|string[]
     */
    protected array $keyValue;

    /**
     * Use for complementing year
     * @var Carbon
     */
    protected Carbon $innerDateUTC;

    /**
     * This will cause the booking to be pushed down on addition
     */
    const DEFAULT_SORTING_RANK = 1000;
    protected BookingOcr $ocr;

    public function __construct(BookingOcr $ocr)
    {
        $this->ocr = $ocr;
    }

    /**
     * Creates an importation log
     *
     * @param string $function
     * @param string $message
     * @return void
     */
    protected function log(string $function, string $message )
    {
        $this->ocr->booking_ocr_importation_logs()->create([
            'function_name' => $function,
            'log' =>  $message
        ]);
    }

    /**
     * @var Itinerary
     */
    protected Itinerary $itinerary;

    public function name():string
    {
        return basename(Str::of(get_class($this))->replace("\\", "/"));
//        return get_class($this) . '/' . __CLASS__;
    }

    /**
     * @param string $readerClassName
     * @param BookingOcr $ocr
     * @return IDocumentReader
     */
    public static function resolveReader( string $readerClassName, BookingOcr $ocr): IDocumentReader
    {
        return new $readerClassName($ocr);
    }

    /**
     * @param array $lines
     * @param string $keyWord
     * @return int
     * @throws ItemNotFoundException
     */
    public static function getLineIndexStartingWith(array $lines, string $keyWord): int
    {
        foreach ($lines as $index => $line )
        {
            if( Str::of(strtolower($line))->startsWith(strtolower($keyWord)) )
                return $index;
        }
       throw new ItemNotFoundException( __FUNCTION__ . " $keyWord was not found!" );
    }

    /**
     * @param array $lines
     * @param string $keyWord
     * @param int $start_index
     * @return int
     */
    public static function getLineIndexStartingWithIndex(array $lines, string $keyWord, int $start_index = 0): int
    {
        foreach (collect($lines)->skip( $start_index )->toArray() as $index => $line )
        {
            if( Str::of(strtolower($line))->startsWith(strtolower($keyWord)) )
                return $index;
        }
        return self::INDEX_NOT_FOUND;
    }

    /**
     * @param array $lines
     * @param string $keyWord
     * @param int $start_index
     * @return string|null
     */
    public static function getLineStartingWithIndex(array $lines, string $keyWord, int $start_index = 0): ?string
    {
        $v = self::getLineIndexStartingWithIndex( $lines, $keyWord , $start_index );
        return $v === self::INDEX_NOT_FOUND ? null : $lines[$v];
    }

    /**
     * @param array $lines
     * @param string $keyWord
     * @param int $start_index
     * @return string|null
     */
    public static function getLineContainingWithIndex(array $lines, string $keyWord, int $start_index = 0): ?string
    {
        $v = self::getLineIndexContaining( $lines, $keyWord , $start_index );
        return $v === self::INDEX_NOT_FOUND ? null : $lines[$v];
    }

    /**
     * @param array $lines
     * @param string $keyWord
     * @param int $start_index
     * @return int
     */
    public static function getLineIndexContaining(array $lines, string $keyWord, int $start_index = 0): int
    {
        foreach (collect($lines)->skip( $start_index )->toArray() as $index => $line )
        {
            if( Str::of(strtolower($line))->contains(strtolower($keyWord)) )
                return $index;
        }
        return self::INDEX_NOT_FOUND;
    }

    /**
     * @param array $lines
     * @param string $regex
     * @param int $start_index
     * @return int
     */
    public static function getLineIndexWithRegex(array $lines, string $regex , int $start_index = 0): int
    {
        foreach (collect($lines)->skip( $start_index )->toArray() as $index => $line )
        {
            if( Str::of(strtolower($line))->match($regex)->length() )
            {
                return $index;
            }
        }
        return self::INDEX_NOT_FOUND;
    }

    /**
     * @param array $jsonArray
     * @return array
     */
    public function read(array $jsonArray): array
    {
        $this->lines = $jsonArray['Lines'];
        $this->tables = $jsonArray['Tables'];
        $this->keyValue = $jsonArray['keyValue'];
        $this->itinerary = Itinerary::find($this->ocr->itinerary_id);

        $this->setInnerDateUTC();

        return [];
    }

    /**
     * @return $this
     */
    protected function setInnerDateUTC()
    {
        $this->innerDateUTC = Carbon::now();
        return $this;
    }

    /**
     * This will extract lines between lines using starting with search mode
     *
     * @param array $lines
     * @param string $startKeyword
     * @param string $endKeyword
     * @param int $start_index
     * @return array
     */
    public static function extractBetweenLinesExclusiveUsingStartingWith(array $lines, string $startKeyword , string $endKeyword , int $start_index = 0): array
    {
        $beginIndex = self::getLineIndexStartingWithIndex( $lines, $startKeyword, $start_index );
        if( $beginIndex === self::INDEX_NOT_FOUND ) return [];

        $endIndex = self::getLineIndexStartingWithIndex( $lines, $endKeyword, $beginIndex+1 );
        if( $endIndex === self::INDEX_NOT_FOUND ) return [];

        return collect($lines)->skip($beginIndex+1)->take( $endIndex- ($beginIndex+1) )->toArray();
    }

    /**
     * This will extract lines between lines using starting with search mode
     *
     * @param array $lines
     * @param string $startKeyword
     * @param string $endKeyword
     * @param int $start_index
     * @return array
     */
    public static function extractBetweenLinesInclusiveUsingStartingWith(array $lines, string $startKeyword , string $endKeyword , int $start_index = 0): array
    {
        $beginIndex = self::getLineIndexStartingWithIndex( $lines, $startKeyword, $start_index );
        if( $beginIndex === self::INDEX_NOT_FOUND ) return [];

        $endIndex = self::getLineIndexStartingWithIndex( $lines, $endKeyword, $beginIndex+1 );
        if( $endIndex === self::INDEX_NOT_FOUND ) return [];

        return collect($lines)->skip($beginIndex)->take( $endIndex- $beginIndex )->toArray();
    }

    public function getKeyValueStartingWith(string $startKeyword): ?string
    {
        $detectKey = collect($this->keyValue)->keys()->filter( function ( string $key ) use ($startKeyword){
            return Str::of(strtolower($key))->trim()->startsWith(strtolower($startKeyword));
        })->first();

        return $detectKey? trim($this->keyValue[$detectKey]): null;
    }

    /**
     * @param string $val
     * @return float
     */
    protected function parseCurrencyToFloat(string $val): float
    {
        if(Str::of($val)->contains(",") && Str::of($val)->contains("."))
            return  floatval((string)Str::of($val)->replace(",", ""));

        return floatval((string)Str::of($val)
            ->replace(" ", "")
            ->replace(",", "."));
    }

    /**
     * @param string $val
     * @param string $format
     * @param Carbon|null $default
     * @return Carbon|false
     */
    protected function carbonParseFromString(string $val,
                                             string $format = self::DATE_FORMAT__DD__MMM__YYYY,
                                             ?Carbon $default = null)
    {
        try {
            return Carbon::createFromFormat($format, $val   );
        }catch(\Exception $exception){
            return $default;
        }
    }

    /**
     * @param string $value
     * @return int
     */
    protected function detectCurrencyTypeID(string $value): int
    {
        if( Str::of( $value)->lower()->contains( "eur" ) ) return CurrencyType::EUR;
        if( Str::of( $value)->lower()->contains( "€" ) ) return CurrencyType::EUR;
        if( Str::of( $value)->lower()->contains( "£" ) ) return CurrencyType::GBP;
        return CurrencyType::USD;
    }
}
