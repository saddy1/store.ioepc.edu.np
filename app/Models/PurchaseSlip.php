<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;



class PurchaseSlip extends Model
{
    protected $fillable = [
        'po_sn',
        'po_date',
        'department_id',
        'remarks',
    ];

    protected $casts = [
        'po_date' => 'date',   // ensures Carbon dates (works with orWhereDate / latest)
    ];

    /** Relationships */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseSlipItem::class);
    }

    // If you have a purchases table referencing purchase_slip_id
    public function purchases(): HasMany
   {
         return $this->hasMany(Purchase::class);
    }

    public function purchase_slip():HasOne
{
    return $this->hasOne(Purchase::class);
}



    public function purchase(): HasOne
{
    return $this->hasOne(Purchase::class);
}


    public function getRouteKeyName()
    {
        return 'po_sn';
    }

    /** Optional: quick search scope used in your controller */
    public function scopeSearch($q, ?string $term)
    {
        $s = trim((string) $term);
        if ($s === '') return $q;

        return $q->where('po_sn', 'like', "%{$s}%")
            ->orWhereDate('po_date', $s)
            ->orWhereHas('department', fn($x) => $x->where('name', 'like', "%{$s}%"));
    }
}
