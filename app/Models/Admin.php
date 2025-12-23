<?php
namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\User as Authenticatable;


class Admin extends Authenticatable
{
use HasFactory;


protected $fillable = ['name','email','password','contact'];


protected $hidden = ['password'];


public function setPasswordAttribute($value)
{
$this->attributes['password'] = Hash::needsRehash($value) ? Hash::make($value) : $value;
}
}