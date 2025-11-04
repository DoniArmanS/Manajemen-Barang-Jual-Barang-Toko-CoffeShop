<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyNote extends Model
{
    protected $fillable = ['note_date','content'];
    protected $casts = ['note_date' => 'date'];
}
