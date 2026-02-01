<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;


class Purchase extends Model
{
    protected $fillable = [
        'purchase_sn',
        'purchase_date',
        'supplier_id',
        'purchase_slip_id',
        'department_id',
       
        'remarks',

        // totals
        'sub_total',
        'tax_mode',      // 'VAT' or 'PAN'
        'vat_percent',   // e.g., 13.00
        'vat_amount',
        'grand_total',
        'total_amount',  // keep for backward-compat (mirror grand_total if you want)

        // attachments

        'bill_no',
        'bill_pic',
        'image_path',    // if you still store it here
    ];

    protected $casts = [
        'purchase_date'   => 'date',
        'sub_total'       => 'float',
        'vat_percent'     => 'float',
        'vat_amount'      => 'float',
        'grand_total'     => 'float',
        'total_amount'    => 'float',
    ];

    /** Relationships */


public function getRouteKeyName()
    {
        return 'purchase_sn';
    }    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function slip(): BelongsTo
    {
        return $this->belongsTo(PurchaseSlip::class, 'purchase_slip_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    /** Backward-compatible alias used by your views */
    public function lines(): HasMany
    {
        return $this->items();
    }

    /** Helper to (re)compute totals from items + tax mode */
    public function recomputeTotals(): void
    {
        $sub = $this->items->sum(function ($i) {
            // Prefer explicit line_subtotal if you store it; else qty*rate
            if (!is_null($i->line_subtotal)) {
                return (float)$i->line_subtotal;
            }
            return (float)$i->qty * (float)$i->rate - (float)$i->discount_amount;
        });

        $this->sub_total = $sub;

        $vatPercent = ($this->tax_mode ?? 'PAN') === 'VAT'
            ? (float)($this->vat_percent ?? 13.00)
            : 0.0;

        $vatAmt = round($sub * ($vatPercent / 100), 2);
        $this->vat_amount  = $vatAmt;
        $this->grand_total = round($sub + $vatAmt, 2);

        // keep legacy field in sync if you like
        $this->total_amount = $this->grand_total;
    }
    public function storeEntry(): HasOne
{
    return $this->hasOne(\App\Models\StoreEntry::class);
}


public function slipNumbers(): array
{
    // purchase_items.purchase_slip_item_id -> purchase_slip_items.purchase_slip_id -> purchase_slips.po_sn
    return $this->items()
        ->whereNotNull('purchase_slip_item_id')
        ->with(['slipItem.slip:id,po_sn'])
        ->get()
        ->pluck('slipItem.slip.po_sn')
        ->filter()
        ->unique()
        ->values()
        ->toArray();
}

}
