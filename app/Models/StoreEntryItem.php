<?php

// app/Models/StoreEntryItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function itemCategory()
    {
        return $this->belongsTo(ItemCategory::class, 'item_category_id');
    }

    public function categoryRef()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function entry()
    {
        return $this->belongsTo(StoreEntry::class, 'store_entry_id');
    }

    public function purchaseItem()
    {
        return $this->belongsTo(PurchaseItem::class, 'purchase_item_id');
    }

    public function storeOutItems()
    {
        return $this->hasMany(StoreOutItem::class, 'store_entry_item_id');
    }
}

