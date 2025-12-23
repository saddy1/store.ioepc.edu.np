<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function search(Request $request)
{
    $q = trim($request->get('q', ''));
    
    if (strlen($q) < 1) {
        return response()->json([]);
    }
    
    $employees = Employee::query()
        ->where(function ($query) use ($q) {
            $query->where('full_name', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%")
                  ->orWhere('atten_no', 'like', "%{$q}%");
        })
        ->limit(10)
        ->get(['id', 'full_name', 'email', 'atten_no']);
    
    // Format for the frontend autocomplete
    $results = $employees->map(function ($e) {
        $parts = [$e->full_name];
        if ($e->email) $parts[] = $e->email;
        if ($e->atten_no) $parts[] = "#{$e->atten_no}";
        
        return [
            'id'   => $e->id,
            'text' => implode(' - ', $parts),
        ];
    });
    
    return response()->json($results);
}
    public function index(Request $request)
    {
        $q = Employee::query()->with('department')->latest();
        if ($s = trim($request->get('search',''))) {
            $q->where('full_name','like',"%{$s}%")
              ->orWhere('email','like',"%{$s}%")
              ->orWhere('atten_no','like',"%{$s}%");
        }
        $employees = $q->paginate(15)->appends($request->only('search'));
        return view('Backend.employees.index', compact('employees'));
    }

    public function create()
    {
        return view('Backend.employees.create', [
            'departments' => Department::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'department_id' => ['required','exists:departments,id'],
            'full_name'     => ['required','string','max:150'],
            'contact'       => ['nullable','string','max:30'],
            'atten_no'      => ['nullable','string','max:50'],
            'email'         => ['nullable','email','max:150','unique:employees,email'],
            'password'      => ['required','string','min:6','max:64'], // admin sets first time
            'is_active'     => ['nullable','boolean'],
        ]);

        Employee::create([
            'department_id'       => $data['department_id'],
            'full_name'           => $data['full_name'],
            'contact'             => $data['contact'] ?? null,
            'atten_no'            => $data['atten_no'] ?? null,
            'email'               => $data['email'] ?? null,
            'password'            => Hash::make($data['password']),
            'must_change_password'=> true,   // first login must change
            'is_active'           => (bool)($data['is_active'] ?? true),
        ]);

        return redirect()->route('employees.index')->with('success','Employee created.');
    }

    public function edit(Employee $employee)
    {
        return view('Backend.employees.edit', [
            'employee'    => $employee,
            'departments' => Department::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'department_id' => ['required','exists:departments,id'],
            'full_name'     => ['required','string','max:150'],
            'contact'       => ['nullable','string','max:30'],
            'atten_no'      => ['nullable','string','max:50'],
            'email'         => ['nullable','email','max:150', Rule::unique('employees','email')->ignore($employee->id)],
            'password'      => ['nullable','string','min:6','max:64'], // admin can reset
            'is_active'     => ['nullable','boolean'],
            'must_change_password' => ['nullable','boolean'],
        ]);

        $payload = [
            'department_id' => $data['department_id'],
            'full_name'     => $data['full_name'],
            'contact'       => $data['contact'] ?? null,
            'atten_no'      => $data['atten_no'] ?? null,
            'email'         => $data['email'] ?? null,
            'is_active'     => (bool)($data['is_active'] ?? $employee->is_active),
        ];

        if (!empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
            // If admin resets, you may force change again:
            $payload['must_change_password'] = true;
        } elseif (array_key_exists('must_change_password',$data)) {
            $payload['must_change_password'] = (bool)$data['must_change_password'];
        }

        $employee->update($payload);

        return redirect()->route('employees.index')->with('success','Employee updated.');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();
        return back()->with('success','Employee deleted.');
    }
}
