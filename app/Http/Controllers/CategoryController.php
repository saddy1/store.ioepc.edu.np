<?php
namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;



class CategoryController extends Controller
{
   public function index(Request $request)
{
    $query = Category::query();

    // Search filter
    if ($request->filled('search')) {
        $search = $request->string('search');
        $query->where('name', 'like', "%{$search}%");
    }

    $categories = $query->latest()->paginate(10)->appends($request->only('search'));

    return view('Backend.categories.index', compact('categories'));
}


    public function create()
    {
        return view('Backend.categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'is_active' => 'nullable|boolean',
        ]);

        Category::create([
            'name' => $request->name,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('product_categories.index')->with('success', 'Category created successfully');
    }

public function edit(Category $product_category)

{
  $category = $product_category;
    return view('Backend.categories.edit', compact('category'));
}


  public function update(Request $request, Category $product_category)
{
    // Normalize input
    $request->merge([
        'name' => trim((string) $request->name),
    ]);

    $validated = $request->validate([
        'name' => [
            'required',
            'string',
            'max:255',
            Rule::unique('categories', 'name')->ignore($product_category->id),
            // ->withoutTrashed(), // uncomment if Category uses SoftDeletes
        ],
        'is_active' => ['nullable', 'boolean'],
    ]);

    $product_category->update([
        'name'      => $validated['name'],
        'is_active' => $request->boolean('is_active'),
    ]);

    return redirect()
        ->route('product_categories.index')
        ->with('success', 'Category updated successfully.');
}


    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->route('product_categories.index')->with('success', 'Category deleted successfully');
    }
}
