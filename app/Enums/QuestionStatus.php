<?php

namespace App\Enums;

enum QuestionStatus: string
{
    case Answered = 'answered';
    case Unanswered = 'unanswered';
}