<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreEntryItem extends Model
{
    protected $fillable = [
        'store_entry_id','purchase_item_id','product_id',
        'item_category_id','category_id','brand_id',
        'item_name','item_sn','unit','qty','rate','total_price',
    ];

    protected $casts = [
        'qty' => 'float',
        'rate' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function entry(): BelongsTo { return $this->belongsTo(StoreEntry::class, 'store_entry_id'); }
    public function purchaseItem(): BelongsTo { return $this->belongsTo(PurchaseItem::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function itemCategory(): BelongsTo { return $this->belongsTo(ItemCategory::class, 'item_category_id'); }
    public function category(): BelongsTo { return $this->belongsTo(Category::class, 'category_id'); }
    public function brand(): BelongsTo { return $this->belongsTo(Brand::class, 'brand_id'); }
    public function categoryRef(){ return $this->belongsTo(Category::class,'category_id'); }

}
