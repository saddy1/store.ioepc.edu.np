<?php

namespace App\Http\Controllers;
use App\Models\Student; 

use Illuminate\Http\Request;

class FormController extends Controller
{
    public function showForm($roll)
{
    $data = Student::where('roll_num', $roll)->get();

    // echo "<pre>";
    // print_r($data->toArray());
    // echo "</pre>";
    // die;

    return view('frontend.dashboard.form', compact('data'));
}
function VerifyForm($token)
{
    $data = Student::where('token_num', $token)->firstOrFail();    // echo "<pre>";
    // print_r($data->toArray());
    // echo "</pre>";
    // die;

    return view('Frontend.dashboard.application',compact('data'));

  
}
}