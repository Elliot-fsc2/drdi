<?php

namespace App\Enums;

enum PanelistRole: string
{
    case CHAIRPERSON = 'chairperson';
    case GUEST_PANEL = 'guest_panel';
    case MEMBER = 'member';
}
