<?php

namespace App\Enums;

enum StudentClassStatus: string
{
    case Approved = 'approved';
    case Pending = 'pending';
    case Denied = 'denied';
}
