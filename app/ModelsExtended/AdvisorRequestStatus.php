<?php

namespace App\ModelsExtended;

class AdvisorRequestStatus extends \App\Models\AdvisorRequestStatus
{
    protected $guarded = ['*'];

    public const DRAFT = 1;
    public const PAID = 2; // PAID_AND_PENDING_ACCEPTANCE
    public const ACCEPTED = 3;
    public const COMPLETED = 4;
    public const REFUNDED = 5;
    public const PENDING = 6;
    public const CANCELLED = 7;// If a advisor cancelled the request
}
