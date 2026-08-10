<?php

namespace App\Enums;

enum QuestionType: string
{
    case MultipleChoice = 'mcq';
    case MultipleAnswer = 'multi_answer';
    case TrueFalse = 'true_false';
    case ShortAnswer = 'short_answer';
}
