<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name_en', 'name_np', 'type'];

    // type: 0 = consumable, 1 = non-consumable
    public function isConsumable(): bool
    {
        return (int) $this->type === 0;
    }

    public function isNonConsumable(): bool
    {
        return (int) $this->type === 1;
    }

    public function typeLabel(): string
    {
        return $this->isConsumable() ? 'Consumable' : 'Non-Consumable';
    }
}
