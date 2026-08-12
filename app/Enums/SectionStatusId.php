<?php

namespace App\Enums;

enum SectionStatusId: int
{
    case Active = 1;
    case Deleted = 2;
    case Archived = 3;
}
