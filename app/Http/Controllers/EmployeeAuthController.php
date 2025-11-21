<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;

class EmployeeAuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('employee')->check()) {
            return redirect()->route('employee.dashboard');
        }
        return view('Frontend.employee.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required','email'],
            'password' => ['required','string'],
        ]);

        if (!Auth::guard('employee')->attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages(['email' => 'Invalid credentials.']);
        }

        $request->session()->regenerate();

        $user = Auth::guard('employee')->user();
        if (!$user->is_active) {
            Auth::guard('employee')->logout();
            throw ValidationException::withMessages(['email' => 'Your account is disabled.']);
        }

        if ($user->must_change_password) {
            return redirect()->route('employee.password.show');
        }

        return redirect()->route('employee.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::guard('employee')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('employee.login');
    }

    public function showChangePassword()
    {
        return view('Frontend.employee.auth.change_password');
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required','string'],
            'password'         => ['required','string','min:6','max:64','confirmed'],
        ]);

        $user = Auth::guard('employee')->user();

        if (!Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->update([
            'password' => Hash::make($data['password']),
            'must_change_password' => false,
        ]);

        return redirect()->route('employee.dashboard')->with('success','Password changed successfully.');
    }
}
