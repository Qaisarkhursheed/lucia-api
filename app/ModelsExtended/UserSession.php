<?php

namespace App\ModelsExtended;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class UserSession extends \App\Models\UserSession
{
    /**
     * @param string|null $token
     * @return Builder|Model|object|null|UserSession
     */
    public static function getByToken(?string $token)
    {
        return self::query()->where("token", $token)->first();
    }
}
