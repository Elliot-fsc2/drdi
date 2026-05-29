<?php

namespace App\Enums;

enum PostType: string
{
    case INSTRUCTORS = 'instructors';
    case STUDENTS = 'students';
    case SECTIONS = 'sections';
}
