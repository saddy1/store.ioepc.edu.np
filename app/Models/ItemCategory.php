<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_en',
        'name_np',
        'type', 
    ];
    public function isConsumable(): bool
    {
        return strcasecmp((string)$this->type, 'Consumable') === 0;
    }

    public function isNonConsumable(): bool
    {
        return !$this->isConsumable();
    }
}
