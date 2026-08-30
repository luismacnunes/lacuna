<?php

namespace App\Enums;

enum FailureReason: string
{
    case NoRelevantChunks = 'no_relevant_chunks';
    case NotSupported = 'not_supported';
    case UserRejected = 'user_rejected';
}