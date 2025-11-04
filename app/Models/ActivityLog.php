<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'log_date','source','action','item_name','qty_change','note','meta'
    ];

    protected $casts = [
        'log_date' => 'date',
        'meta' => 'array',
    ];
}
