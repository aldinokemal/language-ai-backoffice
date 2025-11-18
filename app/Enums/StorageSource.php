<?php


namespace App\Enums;

use App\Classes\EnumConcern;

enum StorageSource: string
{
    use EnumConcern;

    case S3 = 's3'; // aws s3
    case LOCAL = 'local'; // local storage
    case PUBLIC = 'public'; // public storage
}
