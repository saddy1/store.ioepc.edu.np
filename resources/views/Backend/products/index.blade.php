@extends('Backend.layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
  <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <h1 class="text-2xl font-bold">Products</h1>
    <a href="{{ route('products.create') }}"
       class="inline-flex items-center rounded-xl bg-gray-900 text-white px-4 py-2.5 text-sm font-semibold hover:bg-gray-800">
      + Add Product
    </a>
  </div>

  <form method="GET" action="{{ route('products.index') }}" class="mb-6">
    <div class="flex flex-col sm:flex-row gap-3 sm:items-center">
      <input type="text" name="search" value="{{ request('search') }}"
             class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400"
             placeholder="Search by name or SKU...">
      <button class="rounded-xl bg-blue-600 text-white px-5 py-2.5 text-sm hover:bg-blue-700">Search</button>
    </div>
  </form>

  <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
    <table class="w-full text-left">
      <thead class="bg-gray-50 text-xs uppercase text-gray-600">
        <tr>
          <th class="px-4 py-3 w-16">S.N</th>
          <th class="px-4 py-3">Image</th>
          <th class="px-4 py-3">Name (SKU)</th>
          <th class="px-4 py-3">Category / Brand</th>
          <th class="px-4 py-3">Unit</th>
          <th class="px-4 py-3">Stock</th>
          <th class="px-4 py-3 text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        @php $sn = ($products->currentPage() - 1) * $products->perPage() + 1; @endphp
        @forelse($products as $p)
          @php
            // If you used withSum in controller:
            $stock = (float)($p->qty_in ?? 0) - (float)($p->qty_out ?? 0);
          @endphp
          <tr class="hover:bg-gray-50">
            <td class="px-4 py-3">{{ $sn++ }}</td>
            <td class="px-4 py-3">
              @if($p->imageUrl())
                <img src="{{ $p->imageUrl() }}" class="h-10 w-10 rounded object-cover border">
              @else
                <div class="h-10 w-10 rounded border bg-gray-50"></div>
              @endif
            </td>
            <td class="px-4 py-3">
              <div class="font-medium text-gray-900">{{ $p->name }}</div>
              <div class="text-xs text-gray-500">{{ $p->sku }}</div>
            </td>
            <td class="px-4 py-3 text-gray-700">
              <div>{{ $p->productCategory->name ?? '—' }}</div>
              <div class="text-xs text-gray-500">{{ $p->brand->name ?? '—' }}</div>
            </td>
            <td class="px-4 py-3">{{ $p->unit }}</td>
            <td class="px-4 py-3">{{ number_format($stock, 3) }}</td>
            <td class="px-4 py-3 text-right space-x-2">
              <a href="{{ route('products.edit', $p) }}" class="text-blue-600 hover:underline text-sm">Edit</a>
              <form action="{{ route('products.destroy', $p) }}" method="POST" class="inline" onsubmit="return confirm('Delete product?')">
                @csrf @method('DELETE')
                <button class="text-red-600 hover:underline text-sm">Delete</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="7" class="px-4 py-6 text-center text-gray-500">No products found.</td></tr>
        @endforelse
      </tbody>
    </table>
    <div class="px-4 py-3 border-t bg-gray-50">
      {{ $products->links() }}
    </div>
  </div>
</div>
@endsection
