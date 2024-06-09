<?php

namespace App\ModelsExtended;

use App\ModelsExtended\Interfaces\IDeveloperPresentationInterface;

class ApplicationProductPrice extends \App\Models\ApplicationProductPrice implements IDeveloperPresentationInterface
{
    public const LUCIA_EXPERIENCE_MONTHLY = 1;
    public const LUCIA_EXPERIENCE_YEARLY = 2;
    public const LUCIA_COPILOT_ONLY_MONTHLY = 3;

    /**
     * @param int $id
     * @return ApplicationProductPrice|null
     */
    public static function getById(int $id ): ?ApplicationProductPrice
    {
        return self::find( $id );
    }

    public function presentForDev(): array
    {
        $monthly_price = $this->unit_amount;
        $discount_per_month = 0;
        if( $this->id === self::LUCIA_EXPERIENCE_YEARLY )
        {
            $monthly_price = round($monthly_price/12);
            $discount_per_month = self::getById(self::LUCIA_EXPERIENCE_MONTHLY)->unit_amount - $monthly_price;
        }

        return [
            "id" => $this->id,
            'unit_amount' => $this->unit_amount,
            'monthly_price' => $monthly_price,
            'discount_per_month' => $discount_per_month,
            'recurring' => $this->recurring,
            'description' => $this->description,
            ];
    }
}
