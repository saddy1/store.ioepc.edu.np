<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Department;

class DepartmentController extends Controller
{
    private $departmentModel;

    public function __construct(Department $department)
    {
        $this->departmentModel = $department;
    }

    /**
     * Display a listing of departments.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get all departments with type 0, ordered by latest
        $department = $this->departmentModel
                            ->latest()
                            ->get();

        return view('Backend.departments.index', compact('department'));
    }

    /**
     * Store a newly created department.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
        ]);

        $this->departmentModel->create([
            'name' => $validated['name'],
            'status' => 1, // default active
            'type' => 0,   // default type
        ]);

        Session::flash('message', 'Department successfully added!');
        return redirect()->back();
    }

    /**
     * Update the specified department.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'status' => 'required|boolean',
        ]);

        $department = $this->departmentModel->findOrFail($id);
        $department->update([
            'name' => $validated['name'],
            'status' => $validated['status'],
        ]);

        Session::flash('message', 'Department successfully updated!');
        return redirect()->back();
    }

    /**
     * Remove the specified department.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(int $id)
    {
        $department = $this->departmentModel->findOrFail($id);
        $department->delete();

        Session::flash('message', 'Department successfully deleted!');
        return redirect()->back();
    }
}
