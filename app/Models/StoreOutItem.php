<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class StoreOutItem extends Model
{
    protected $fillable = [
        'store_out_id',
        'store_entry_item_id',
        'item_category_id',
        'category_id',
        'brand_id',
        'item_name',
        'item_sn',
        'unit',
        'qty',
        'rate',
        'total_price',
        'remarks',
        'returned_at',
    ];

    protected $casts = [
        'returned_at' => 'datetime',
        'qty' => 'decimal:3',
        'rate' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function storeOut()
    {
        return $this->belongsTo(StoreOut::class, 'store_out_id');
    }

    public function storeEntryItem()
    {
        return $this->belongsTo(StoreEntryItem::class, 'store_entry_item_id');
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->whereNull('returned_at');
    }
}
