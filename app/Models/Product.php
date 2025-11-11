<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory;
    // use SoftDeletes; // uncomment if you want soft deletes

    protected $fillable = [
        'name', 'sku', 'item_category_id', 'product_category_id', 'brand_id',
        'unit', 'reorder_level', 'image_path', 'is_active',
    ];

    protected $casts = [
        'item_category_id'    => 'integer',
        'product_category_id' => 'integer',
        'brand_id'            => 'integer',
        'reorder_level'       => 'integer',
        'is_active'           => 'boolean',
        // if you ever store money on product table:
        // 'some_amount'      => 'decimal:2',
    ];

    // If you want to always include stock in JSON:
    // protected $appends = ['stock'];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function itemCategory()      { return $this->belongsTo(ItemCategory::class); }
    public function productCategory()   { return $this->belongsTo(Category::class, 'product_category_id'); }
    public function brand()             { return $this->belongsTo(Brand::class); }

    // IN: actual receipts (from purchases)
    public function purchaseLines()     { return $this->hasMany(PurchaseLine::class); }

    // OUT: issues to departments (if you implemented StoreIssue)
    // public function issues()            { return $this->hasMany(StoreIssue::class); }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    // Public URL for image (null if not set)
    public function imageUrl(): ?string
    {
        return $this->image_path ? asset('storage/' . ltrim($this->image_path, '/')) : null;
    }

    // Laravel 10+ “Attribute” accessor (alternative to imageUrl() method)
    protected function imageUrlAttr(): Attribute
    {
        return Attribute::get(fn () => $this->imageUrl());
    }

    // Computed stock = sum(receipts) - sum(issues)
    public function getStockAttribute(): float
    {
        // Keep it simple; you can optimize with withSum() on listing pages.
        $in  = (float) $this->purchaseLines()->sum('qty');
        $out = (float) $this->issues()->sum('qty');
        return $in - $out;
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */
    public function scopeActive($q) { return $q->where('is_active', true); }

    public function scopeSearch($q, ?string $term)
    {
        if (!$term) return $q;
        $term = trim($term);
        return $q->where(function ($x) use ($term) {
            $x->where('name', 'like', "%{$term}%")
              ->orWhere('sku', 'like', "%{$term}%");
        });
    }
}
