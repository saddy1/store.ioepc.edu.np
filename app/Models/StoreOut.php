<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreOut extends Model
{
    protected $fillable = [
        'employee_id',
        'department_id',
        'store_out_sn',
        'store_out_date_bs',
        'remarks',
    ];

    public function items()
    {
        return $this->hasMany(\App\Models\StoreOutItem::class, 'store_out_id');
    }

    public function department()
    {
        return $this->belongsTo(\App\Models\Department::class, 'department_id');
    }

    public function employee()
    {
        return $this->belongsTo(\App\Models\Employee::class, 'employee_id');
    }
}
