<?php
// app/Models/StudentDocument.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentDocument extends Model
{
    protected $table = 'student_documents';
    protected $primaryKey = 'token_num';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'token_num',
        'payment_image',
        'voucher_image',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'token_num', 'token_num');
    }
}
