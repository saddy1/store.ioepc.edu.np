<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Student;

class EnsureStudent
{
    public function handle(Request $request, Closure $next): Response
    {
        // Check if student is logged in
        if (!session()->has('student_id')) {
            return redirect()->route('student.login.form')->with('error', 'Please login as Student.');
        }

        $student = Student::find(session('student_id'));
        if (!$student) {
            session()->forget('student_id');
            return redirect()->route('student.login.form')->with('error', 'Invalid session. Please login again.');
        }

        // Verify roll number if present in route
        $routeRoll = $request->route('roll');
        if ($routeRoll && $routeRoll !== $student->roll_num) {
            abort(403, 'Unauthorized access.');
        }

        // Verify token if present in route
        $routeToken = $request->route('token');
        if ($routeToken && $routeToken !== $student->token_num) {
            abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}
