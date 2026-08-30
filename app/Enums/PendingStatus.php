<?php

namespace App\Enums;

enum PendingStatus: string
{
    case Open = 'open';
    case Answered = 'answered';
    case Dismissed = 'dismissed';
}