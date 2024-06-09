<?php

namespace App\ModelsExtended;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Airline extends \App\Models\Airline
{

    /**
     * It searches with 2 unique code like TK for Turkish Airlines
     *
     * @param string $data
     * @return Builder|Model|Airline
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public static function findByIata( string $data )
    {
        return self::query()->where( 'iata', $data )->firstOrFail();
    }

    /**
     * It searches with 3 unique code like THY for Turkish Airlines
     *
     * @param string $data
     * @return Builder|Model|Airline
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public static function findByIcao( string $data )
    {
        return self::query()->where( 'icao',  $data )->firstOrFail();
    }
}
