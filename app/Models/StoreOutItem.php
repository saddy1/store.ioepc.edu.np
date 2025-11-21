<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    ];

    public function storeOut(): BelongsTo
    {
        return $this->belongsTo(StoreOut::class);
    }

    public function storeEntryItem(): BelongsTo
    {
        return $this->belongsTo(StoreEntryItem::class);
    }

    public function itemCategory(): BelongsTo
    {
        return $this->belongsTo(ItemCategory::class);
    }
}
