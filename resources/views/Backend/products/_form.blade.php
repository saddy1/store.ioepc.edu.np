{{-- resources/views/backend/products/_form.blade.php --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
  <div>
    <label class="block text-sm font-medium mb-1">Name *</label>
    <input name="name" value="{{ old('name', $product->name ?? '') }}" required
           class="w-full rounded-lg border-gray-300 focus:border-gray-400 focus:ring-0 px-3 py-2">
  </div>
  <div>
    <label class="block text-sm font-medium mb-1">SKU *</label>
    <input name="sku" value="{{ old('sku', $product->sku ?? '') }}" required
           class="w-full rounded-lg border-gray-300 focus:border-gray-400 focus:ring-0 px-3 py-2">
  </div>

  <div>
    <label class="block text-sm font-medium mb-1">Item Category</label>
    <select name="item_category_id" class="w-full rounded-lg border-gray-300 focus:border-gray-400 focus:ring-0 px-3 py-2">
      <option value="">-- Select --</option>
      @foreach($itemCategories as $c)
        <option value="{{ $c->id }}" @selected(old('item_category_id', $product->item_category_id ?? null) == $c->id)>{{ $c->name_en }} @if($c->name_np) ({{ $c->name_np }}) @endif</option>
      @endforeach
    </select>
  </div>

  <div>
    <label class="block text-sm font-medium mb-1">Product Category</label>
    <select name="product_category_id" class="w-full rounded-lg border-gray-300 focus:border-gray-400 focus:ring-0 px-3 py-2">
      <option value="">-- Select --</option>
      @foreach($productCategories as $c)
        <option value="{{ $c->id }}" @selected(old('product_category_id', $product->product_category_id ?? null) == $c->id)>{{ $c->name }}</option>
      @endforeach
    </select>
  </div>

  <div>
    <label class="block text-sm font-medium mb-1">Brand</label>
    <select name="brand_id" class="w-full rounded-lg border-gray-300 focus:border-gray-400 focus:ring-0 px-3 py-2">
      <option value="">-- Select --</option>
      @foreach($brands as $b)
        <option value="{{ $b->id }}" @selected(old('brand_id', $product->brand_id ?? null) == $b->id)>{{ $b->name }}</option>
      @endforeach
    </select>
  </div>

  <div>
    <label class="block text-sm font-medium mb-1">Unit *</label>
    <input name="unit" value="{{ old('unit', $product->unit ?? 'pcs') }}" required
           class="w-full rounded-lg border-gray-300 focus:border-gray-400 focus:ring-0 px-3 py-2">
  </div>

  <div>
    <label class="block text-sm font-medium mb-1">Reorder Level</label>
    <input type="number" min="0" name="reorder_level" value="{{ old('reorder_level', $product->reorder_level ?? 0) }}"
           class="w-full rounded-lg border-gray-300 focus:border-gray-400 focus:ring-0 px-3 py-2">
  </div>

  <div class="sm:col-span-2">
    <label class="block text-sm font-medium mb-1">Image</label>
    <input type="file" name="image" accept="image/*"
           class="block w-full rounded-lg border-gray-300 focus:border-gray-400 focus:ring-0 px-3 py-2">
    @isset($product)
      @if($product->imageUrl())
        <img src="{{ $product->imageUrl() }}" class="mt-2 h-24 rounded-md border">
      @endif
    @endisset
  </div>

  <div class="sm:col-span-2 flex items-center gap-2">
    <input type="checkbox" id="is_active" name="is_active" value="1"
           {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }}
           class="h-4 w-4 text-blue-600 border-gray-300 rounded">
    <label for="is_active" class="text-sm">Active</label>
  </div>
</div>

<div class="flex flex-col sm:flex-row justify-end gap-3 pt-4">
  <a href="{{ route('products.index') }}" class="rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</a>
  <button class="rounded-xl bg-gray-900 text-white px-5 py-2.5 text-sm font-semibold hover:bg-gray-800">
    {{ isset($product) ? 'Update Product' : 'Save Product' }}
  </button>
</div>
