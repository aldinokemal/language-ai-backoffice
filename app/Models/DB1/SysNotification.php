<?php

namespace App\Models\DB1;

use Illuminate\Notifications\DatabaseNotification;

class SysNotification extends DatabaseNotification
{
    protected $table = 'sys_notifications';

    protected $casts = [
        'data' => 'array',
    ];
}
