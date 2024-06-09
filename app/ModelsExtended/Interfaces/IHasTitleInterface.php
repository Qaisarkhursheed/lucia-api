<?php

namespace App\ModelsExtended\Interfaces;


/**
 * Class IHasTitleInterface
 *
 * @package App\ModelsExtended
 */
interface IHasTitleInterface
{
    /**
     * @return string
     */
   public function title(): ?string;
}
