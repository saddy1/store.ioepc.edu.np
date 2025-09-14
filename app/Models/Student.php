<?php
namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Student extends Model
{
use HasFactory;


protected $fillable = [
    'token_num', 'roll_num', 'name','faculty', 'batch', 'subject', 'year', 'part', 'payment_id', 'status'
];
}