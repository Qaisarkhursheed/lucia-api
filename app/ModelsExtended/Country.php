<?php

namespace App\ModelsExtended;

use App\Models\DbTimezone;
use App\ModelsExtended\Interfaces\IDeveloperPresentationInterface;

/**
 * @property DbTimezone|null $timezone
 */
class Country extends \App\Models\Country implements IDeveloperPresentationInterface
{
    public const US = 233;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function timezone(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
       return $this->hasOne(DbTimezone::class, "country_name", "description");
    }

    public function presentForDev(): array
    {
       return [
           "id" => $this->id,
           "description" => $this->description,
           "iso_3166_1_alpha2_code" => $this->iso_3166_1_alpha2_code,
           "offset_gmt" => optional($this->timezone)->offset_gmt,
           "timezone_id" => optional($this->timezone)->timezone_id,
           "offset_tzab" => optional($this->timezone)->offset_tzab,
       ];
    }
}