<?php

namespace App\ModelsExtended;

class OcrStatus extends \App\Models\OcrStatus
{
    public const QUEUED = 1;
    public const INITIALIZED = 2;
    public const RECOGNIZING = 3;
    public const FAILED_RECOGNITION = 4;
    public const COMPLETED_RECOGNITION = 5;
    public const FAILED_IMPORTATION = 6;
    public const IMPORTED = 7;
    public const IMPORTING = 8 ;
}
