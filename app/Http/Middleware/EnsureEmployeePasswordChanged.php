<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureEmployeePasswordChanged
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::guard('employee')->user();
        if ($user && $user->must_change_password && !$request->routeIs('employee.password.show','employee.password.update','employee.logout')) {
            return redirect()->route('employee.password.show')
                ->with('warning', 'Please change your password to continue.');
        }
        return $next($request);
    }
}
