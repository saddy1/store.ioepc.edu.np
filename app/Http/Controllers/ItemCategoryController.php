<?php

namespace App\Http\Controllers;

use App\Models\ItemCategory;
use Illuminate\Http\Request;

class ItemCategoryController extends Controller
{
    public function index()
    {
        // Use pagination so you can safely call ->total() in the view
        $categories = ItemCategory::latest()->paginate(10);
        return view('Backend.items.index', compact('categories'));
    }

    public function create()
    {
        return view('Backend.items.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name_en' => 'required|string|max:100',
            'name_np' => 'nullable|string|max:100',
        ]);

        ItemCategory::create($data);
        return redirect()->route('categories.index')->with('success', 'Item category added successfully.');
    }

    public function destroy(ItemCategory $category)
    {
        $category->delete();
        return redirect()->back()->with('success', 'Item category deleted successfully.');
    }

   public function edit(ItemCategory $category) // <-- type-hinted model
    {
        return view('Backend.items.edit', compact('category'));
    }

   public function update(Request $request, ItemCategory $category)
    {
        $data = $request->validate([
            'name_en' => 'required|string|max:100',
            'name_np' => 'nullable|string|max:100',
        ]);

        $category->update($data);

        return redirect()->route('categories.index')
            ->with('success', 'Item category updated successfully.');
    
}
}
