<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryTransaction extends Model
{
    protected $fillable = ['item_id','qty_change','reason'];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
