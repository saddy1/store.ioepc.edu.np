<?php

namespace App\Http\Controllers;


use App\Models\Admin;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
    // STUDENT
    public function showStudentLogin()

    {
        if (session()->has('student_id')) {
            return redirect()->route('student.dashboard');
        }
        return view('Frontend.auth.student-login');
    }




    // ADMIN
    public function showAdminLogin()
    {
        if (session()->has('admin_id')) {
            return redirect()->route('admin.dashboard');
        }
        return view('Backend.index');
    }


    public function adminLogin(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6'],
        ]);


        $admin = Admin::where('email', $request->email)->first();
        if (!$admin || !Hash::check($request->password, $admin->password)) {
           return back()
        ->withErrors(['email' => 'Invalid email or password.'])
        ->withInput();
        }


        session(['admin_id' => $admin->id]);
        return redirect()->route('admin.dashboard');
    }


    // LOGOUT (shared)
    public function logout()
    {
        session()->forget(['admin_id', 'student_id']);
        return redirect()->route('home')->with('success', 'Logged out successfully.');
    }
}
