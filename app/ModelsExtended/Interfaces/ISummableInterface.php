<?php

namespace App\ModelsExtended\Interfaces;

interface ISummableInterface
{
    /**
     * Return the price of this item
     * @return float
     */
    public function getSum():float;
}
