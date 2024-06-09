<?php

namespace App\ModelsExtended;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RequestAvailableDiscount extends \App\Models\RequestAvailableDiscount
{

    /**
     * @param string $code
     * @return Builder|Model|object|null|RequestAvailableDiscount
     */
    public static function getByCode(string $code)
    {
        return self::query()->where("description", $code)->first();
    }

    /**
     * @param string $discount_code
     * @param AdvisorRequest $advisorRequest
     * @return RequestAvailableDiscount|Builder|Model|object
     * @throws \Exception
     */
    public static function getAvailableDiscount(string $discount_code, AdvisorRequest $advisorRequest)
    {
        $d = RequestAvailableDiscount::getByCode($discount_code );
        if( !$d || !$d->is_active ) throw new \Exception("Invalid discount code provided!");

        if( $advisorRequest->sub_amount < $d->limit_purchase_amount )
            throw new \Exception("You have to make a minimum purchase of " . number_format($d->limit_purchase_amount, 2) . "USD to use this discount code!");

        //if( $advisorRequest->sub_amount <= $d->discount )
          //  throw new \Exception("You have to make a minimum purchase of an amount greater than " . number_format($d->discount, 2) . "USD to use this discount code!");

        if( AdvisorRequest::getDiscountUsageCount( $advisorRequest->user->id, $discount_code ) >= $d->limit_to_usage_count )
            throw new \Exception("You can not use this discount code more than " . $d->limit_to_usage_count . " " . Str::plural("time", $d->limit_to_usage_count ));

        return $d;
    }
}