<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class PurchaseSlipItem extends Model
{
    protected $fillable = [
        'purchase_slip_id',
        'temp_name',
        'temp_sn',
        'ordered_qty',
        'max_rate',
        'line_total',
        'unit',
        'item_category_id',
    ];

    protected $casts = [
        'ordered_qty' => 'float',
        'max_rate'    => 'float',
        'line_total'  => 'float',
    ];

    /** Automatically calculate total before save */
    protected static function booted(): void
    {
        static::saving(function (self $item) {
            $item->line_total = (float)$item->ordered_qty * (float)$item->max_rate;
        });
    }

    /** Relationship to parent slip */
    public function slip(): BelongsTo
    {
        return $this->belongsTo(PurchaseSlip::class, 'purchase_slip_id');
    }
    public function itemCategory(): BelongsTo
    {
        return $this->belongsTo(ItemCategory::class, 'item_category_id');
    }
    public function purchaseItem()
{
  return $this->hasOne(PurchaseItem::class, 'purchase_slip_item_id');
}

}
