<?php

namespace App\ModelsExtended;

class BookingCategory extends \App\Models\BookingCategory
{
    public const Flight = 1;
    public const Hotel = 2;
    public const Concierge = 3;
    public const Cruise = 4;
    public const Transportation = 5;
    public const Tour_Activity = 6;
    public const Insurance = 7;
    public const Other_Notes = 8;
    public const Header = 9;
    public const Divider = 10;
}
