<?php

namespace App\ModelsExtended\Interfaces;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property Carbon $created_at
 * @property int $sorting_rank
 * @method  bool updateQuietly(array $attributes = [], array $options = [])
 */
interface IShareableSortableInterface
{
    const SORT_DATE_FORMAT = "Y-m-d";
    /**
     * Locale Date not UTC from Database
     * Sorting Property
     * @return Carbon
     */
    public function sortByKey(): Carbon;
}
