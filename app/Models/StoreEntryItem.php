<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreEntryItem extends Model
{
    protected $fillable = [
        'store_entry_id',
        'purchase_item_id',
        'product_id',
        'item_category_id',
        'category_id',
        'brand_id',
        'item_name',
        'item_sn',
        'unit',
        'qty',
        'rate',
        'total_price',
    ];

    // ✅ already existing
    public function itemCategory(): BelongsTo
    {
        return $this->belongsTo(ItemCategory::class, 'item_category_id');
    }

    // ✅ already existing (your controller uses this)
    public function categoryRef(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function entry(): BelongsTo
    {
        return $this->belongsTo(StoreEntry::class, 'store_entry_id');
    }

    public function purchaseItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseItem::class, 'purchase_item_id');
    }

    // ✅ FIX: controller calls ->with('product')
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    // ✅ FIX: ledger() calls ->with('category')
    // Make alias to the same table as categoryRef
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function storeOutItems()
    {
        return $this->hasMany(StoreOutItem::class, 'store_entry_item_id');
    }
    
    
}
