<?php

namespace App\ModelsExtended\Interfaces;


/**
 * Class ICalenderRenderCompatibleInterface
 *
 * @package App\ModelsExtended
 */
interface ICalenderRenderCompatibleInterface extends IHasTitleInterface
{
    /**
     * @return array
     */
   public function formatForCalendar();
}
