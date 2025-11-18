<?php


namespace App\Enums;

use App\Classes\EnumConcern;

enum BuiltInRole: string
{
    use EnumConcern;

    case SUPER_ADMIN = 'Super Admin';
}
