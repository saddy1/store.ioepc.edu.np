<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StoreOut extends Model
{
    protected $fillable = [
        'employee_id',
        'store_entry_id',
        'store_out_sn',
        'store_out_date_bs',
        'remarks',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function entry(): BelongsTo
    {
        return $this->belongsTo(StoreEntry::class, 'store_entry_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StoreOutItem::class);
    }
}
