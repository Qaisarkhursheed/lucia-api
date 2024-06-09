<?php

namespace App\ModelsExtended;

class AdvisorRequestType extends \App\Models\AdvisorRequestType
{
    /**
     * @param int $id
     * @return AdvisorRequestType
     */
    public static function getById( int $id )
    {
        return self::find($id);
    }
}
