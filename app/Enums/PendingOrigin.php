<?php

namespace App\Enums;

enum PendingOrigin: string
{
    case Ingestion = 'ingestion';
    case RealFailure = 'real_failure';
}