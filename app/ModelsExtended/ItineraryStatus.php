<?php

namespace App\ModelsExtended;

class ItineraryStatus extends \App\Models\ItineraryStatus
{
    public const Sent = 1;
    public const Declined = 2;
    public const Accepted = 3;
    public const Draft = 4;
}
