<?php

namespace App\ModelsExtended;

use Illuminate\Database\Eloquent\Collection;

/**
* @property Collection|ApplicationProductPrice[] $application_product_prices
 */
class ApplicationProduct extends \App\Models\ApplicationProduct
{
    public const LUCIA = 1;

    public function application_product_prices()
    {
        return $this->hasMany(ApplicationProductPrice::class);
    }
}
