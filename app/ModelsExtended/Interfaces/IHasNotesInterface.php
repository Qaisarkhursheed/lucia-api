<?php

namespace App\ModelsExtended\Interfaces;


/**
 * Class IHasDescriptionInterface
 *
 * @package App\ModelsExtended
 */
interface IHasNotesInterface
{
    /**
     * @return ?string
     */
   public function notes(): ?string;
}
