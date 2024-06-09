<?php

namespace App\Http\Controllers\Enhancers;

use Illuminate\Database\Eloquent\Collection;
use Reliese\Database\Eloquent\Model;

interface IYajraEloquentResultProcessorInterface
{
    /**
     * @param Collection|\Illuminate\Support\Collection|Model[] $result
     * @return array
     */
    public function processYajraEloquentResult( $result ) :array;

}
