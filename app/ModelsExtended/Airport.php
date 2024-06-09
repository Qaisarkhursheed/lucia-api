<?php

namespace App\ModelsExtended;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Airport extends \App\Models\Airport
{
    /**
     * It searches with 3 unique code like IST for ISTANBUL
     *
     * @param string $data
     * @return Builder|Model|Airport
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public static function findByIata( string $data )
    {
        return self::query()->where( 'iata', $data )->firstOrFail();
    }

    /**
     * It searches with 3 unique code like IST for ISTANBUL
     *
     * @param string $name
     * @return Builder|Model|Airport|null
     */
    public static function findByName( string $name )
    {
        return self::query()->where( 'name', $name )->first();
    }
}
