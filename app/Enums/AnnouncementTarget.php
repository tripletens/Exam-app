<?php

namespace App\Enums;

enum AnnouncementTarget: string
{
    case All = 'all';
    case Interns = 'intern';
    case Instructors = 'instructor';
}
