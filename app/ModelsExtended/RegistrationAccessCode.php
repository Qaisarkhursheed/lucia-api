<?php

namespace App\ModelsExtended;

use Illuminate\Support\Str;

class RegistrationAccessCode extends \App\Models\RegistrationAccessCode
{
    /**
     * @param string $code
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null|RegistrationAccessCode
     */
    public static function getByCode(string $code)
    {
        return self::query()->where("code", $code)->first();
    }

    /**
     * @return mixed|RegistrationAccessCode
     */
    public static function generateCode()
    {
        return self::create(
            [
                "code" => Str::random(10)
            ]
        );
    }
}
