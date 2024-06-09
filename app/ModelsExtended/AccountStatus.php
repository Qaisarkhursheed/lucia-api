<?php

namespace App\ModelsExtended;

class AccountStatus extends \App\Models\AccountStatus
{
    public const PENDING_APPROVAL = 1;
    public const APPROVED = 2;
    public const REJECTED = 3;
}
