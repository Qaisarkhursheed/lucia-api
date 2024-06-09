<?php

namespace App\ModelsExtended;

class TransitType extends \App\Models\TransitType
{
    public const Rail = 1;
    public const Ferry = 2;
    public const Car = 3;
    public const Transfer = 4;
}
