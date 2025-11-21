<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable; // so we can log them in
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Employee extends Authenticatable
{
    protected $fillable = [
        'department_id',
        'full_name',
        'contact',
        'atten_no',
        'email',
        'password',
        'must_change_password',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'must_change_password' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    // Use email for auth if provided; otherwise you can pivot to atten_no later
    public function getAuthIdentifierName()
    {
        return 'email';
    }
}
