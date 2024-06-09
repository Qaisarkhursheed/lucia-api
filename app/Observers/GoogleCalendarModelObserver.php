<?php

namespace App\Observers;

use App\ModelsExtended\Interfaces\ICanCreateGoogleCalendarEventInterface;
use App\ModelsExtended\ModelBase;
use Illuminate\Support\Facades\Log;

class GoogleCalendarModelObserver
{
    /**
     * Perform Manipulations
     * @param ModelBase $modelBase
     */
    public function saving( ModelBase $modelBase )
    {
        if( $modelBase instanceof ICanCreateGoogleCalendarEventInterface  )
            $modelBase->createCalendarEvent(false);
    }

    /**
     * Perform Manipulations
     * @param ModelBase $modelBase
     */
    public function deleted( ModelBase $modelBase )
    {
        if( $modelBase instanceof ICanCreateGoogleCalendarEventInterface  )
            $modelBase->deleteCalendarEvent(false);
    }
}
