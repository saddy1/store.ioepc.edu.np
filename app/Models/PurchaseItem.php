<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseItem extends Model
{
    protected $table = 'purchase_items';

protected $fillable = [
    'purchase_id',

    // ✅ add these
    'purchase_slip_id',
    'purchase_slip_item_id',

    'item_category_id',
    'product_id',
    'temp_name',
    'temp_sn',
    'unit',
    'qty',
    'rate',
    'store_entry_sn',
    'store_entry_date',
    'discount_percent',
    'discount_amount',
    'line_subtotal',
    'notes',
];

    protected $casts = [
        'qty'               => 'decimal:3',
        'rate'              => 'decimal:2',

        'discount_percent'  => 'decimal:2',
        'discount_amount'   => 'decimal:2',
        'line_subtotal'     => 'decimal:2',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ItemCategory::class, 'item_category_id');
    }

    public function product(): BelongsTo
    {
        // nullable
        return $this->belongsTo(Product::class);
    }

    public function slipItem()
{
  return $this->belongsTo(PurchaseSlipItem::class, 'purchase_slip_item_id');
}


    protected static function booted(): void
    {
        static::saving(function (self $item) {
            // Default discount_amount if % provided and amount empty
            if ($item->discount_amount == 0 && $item->discount_percent > 0) {
                $gross = (float)$item->qty * (float)$item->rate;
                $item->discount_amount = round($gross * ((float)$item->discount_percent / 100), 2);
            }

            // Compute line_subtotal if not set: (qty * rate) - discount_amount
            if (is_null($item->line_subtotal)) {
                $item->line_subtotal = round(
                    (float)$item->qty * (float)$item->rate - (float)$item->discount_amount,
                    2
                );
            }
        });
    }
}
