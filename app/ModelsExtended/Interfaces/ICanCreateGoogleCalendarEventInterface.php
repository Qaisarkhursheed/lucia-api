<?php

namespace App\ModelsExtended\Interfaces;


/**
 * Class ICanCreateGoogleCalendarEventInterface
 *
 * @property string $google_calendar_event_id
 * @package App\ModelsExtended
 */
interface ICanCreateGoogleCalendarEventInterface extends IHasTitleInterface, IHasNotesInterface
{
    /**
     * If update quietly is not set, it won't call update at all
     *
     * @param bool $updateQuietly
     * @return mixed
     */
   public function createCalendarEvent( bool $updateQuietly = true);

    /**
     * If update quietly is not set, it won't call update at all
     *
     * @param bool $updateQuietly
     * @return mixed
     */
   public function deleteCalendarEvent( bool $updateQuietly = true);
}
