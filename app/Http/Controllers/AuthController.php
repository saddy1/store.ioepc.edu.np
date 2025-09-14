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


    public function studentLogin(Request $request)
    {
        $request->validate([
            'token_num' => ['required', 'string', 'max:100'],
            'roll_num' => ['required', 'regex:/^PUR\d{3}[A-Za-z]{3}\d{3}$/'],
        ], [
            'roll_num.regex' => 'Roll number must match PUR***###*** (e.g., PUR123ABC456).'
        ]);


        $student = Student::where('token_num', $request->token_num)
            ->where('roll_num', $request->roll_num)
            ->first();


        if (!$student) {
            return back()->withInput()->with('error', 'Invalid token or roll number.');
        }


        session(['student_id' => $student->id]);
        return redirect()->route('student.dashboard');
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
            return back()->withInput()->with('error', 'Invalid credentials.');
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
