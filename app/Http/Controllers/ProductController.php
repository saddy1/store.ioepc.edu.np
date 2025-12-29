<?php

// app/Http/Controllers/ProductController.php
namespace App\Http\Controllers;

use App\Models\{Product, ItemCategory, Category, Brand};
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $q = Product::query()->with(['itemCategory','productCategory','brand']);

        if ($s = $request->string('search')->toString()) {
            $q->where(fn($x)=>$x->where('name','like',"%$s%")->orWhere('sku','like',"%$s%"));
        }

        $products = $q->latest()->paginate(10)->appends($request->only('search'));
        return view('Backend.products.index', compact('products'));
    }

    public function create()
    {
        return view('Backend.products.create', [
            'itemCategories' => ItemCategory::orderBy('name_en')->get(),
            'productCategories' => Category::orderBy('name')->get(),
            'brands' => Brand::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:150'],
            'sku'  => ['required','string','max:50','unique:products,sku'],
            'item_category_id' => ['nullable','exists:item_categories,id'],
            'product_category_id' => ['nullable','exists:categories,id'],
            'brand_id' => ['nullable','exists:brands,id'],
            'unit' => ['required','string','max:20'],
            'reorder_level' => ['nullable','integer','min:0'],
            'image' => ['nullable','image','max:2048'],
            'is_active' => ['nullable','boolean'],
        ]);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('products','public');
        }
        $data['is_active'] = $request->boolean('is_active');

        Product::create($data);

        return redirect()->route('products.index')->with('success','Product created.');
    }

    public function edit(Product $product)
    {
        return view('Backend.products.edit', [
            'product' => $product,
            'itemCategories' => ItemCategory::orderBy('name_en')->get(),
            'productCategories' => Category::orderBy('name')->get(),
            'brands' => Brand::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => ['required','string','max:150'],
            'sku'  => ['required','string','max:50', Rule::unique('products','sku')->ignore($product->id)],
            'item_category_id' => ['nullable','exists:item_categories,id'],
            'product_category_id' => ['nullable','exists:categories,id'],
            'brand_id' => ['nullable','exists:brands,id'],
            'unit' => ['required','string','max:20'],
            'reorder_level' => ['nullable','integer','min:0'],
            'image' => ['nullable','image','max:2048'],
            'is_active' => ['nullable','boolean'],
        ]);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('products','public');
        }
        $data['is_active'] = $request->boolean('is_active');

        $product->update($data);

        return redirect()->route('products.index')->with('success','Product updated.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return back()->with('success','Product deleted.');
    }
}
