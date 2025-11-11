<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
  public function index(Request $request)
{
    $query = Supplier::query();

    // Apply filters
    if ($request->filled('search')) {
        $search = $request->input('search');
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('pan', 'like', "%{$search}%");
        });
    }

    // Get paginated results
    $suppliers = $query->latest()->paginate(10);

    // Keep search term across pagination
    $suppliers->appends($request->only('search'));

    return view('Backend.supplier.index', compact('suppliers'));
}


    public function create()
    {
        return view('Backend.supplier.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'    => ['required','string','max:150'],
            // 9-digit PAN (Nepal). If your college uses different format, adjust regex.
            'pan'     => ['required','regex:/^\d{9}$/', 'unique:suppliers,pan'],
            'address' => ['nullable','string','max:255'],
        ]);

        Supplier::create($data);

        return redirect()->route('suppliers.index')->with('success', 'Supplier created successfully.');
    }

    public function edit(Supplier $supplier)
    {
        return view('Backend.supplier.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $data = $request->validate([
            'name'    => ['required','string','max:150'],
            'pan'     => [
                'required','regex:/^\d{9}$/',
                Rule::unique('suppliers','pan')->ignore($supplier->id),
            ],
            'address' => ['nullable','string','max:255'],
        ]);

        $supplier->update($data);

        return redirect()->route('suppliers.index')->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return redirect()->route('suppliers.index')->with('success', 'Supplier deleted.');
    }
}
