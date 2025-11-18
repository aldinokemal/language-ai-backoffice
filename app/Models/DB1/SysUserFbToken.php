<?php

namespace App\Models\DB1;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SysUserFbToken extends Model
{
    protected $table = 'sys_user_fbtokens';

    protected $fillable = [
        'user_id',
        'token',
        'agent',
        'ip',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(SysUser::class);
    }
}
