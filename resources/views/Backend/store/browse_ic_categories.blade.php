@extends('Backend.layouts.app')
@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
  <div class="mb-6 flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold">Product Categories under: {{ $itemCategoryName }}</h1>
      <div class="text-sm text-gray-500">
        <a href="{{ route('store.browse.ic') }}" class="text-blue-600 hover:underline">← Item Categories</a>
      </div>
    </div>
    <a href="{{ route('store.browse') }}"
       class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
      Browse Home
    </a>
  </div>

  <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
    <table class="w-full text-left">
      <thead class="bg-gray-50 text-xs uppercase text-gray-600">
        <tr>
          <th class="px-4 py-3 w-16">S.N</th>
          <th class="px-4 py-3">Product Category</th>
          <th class="px-4 py-3">Items</th>
          <th class="px-4 py-3">Amount</th>
          <th class="px-4 py-3 text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        @php $sn = 1; @endphp
        @forelse($rows as $r)
          <tr class="hover:bg-gray-50">
            <td class="px-4 py-3">{{ $sn++ }}</td>
            <td class="px-4 py-3 font-medium">{{ $r['category'] }}</td>
            <td class="px-4 py-3">{{ $r['items_count'] }}</td>
            <td class="px-4 py-3">{{ $r['total_amount'] }}</td>
            <td class="px-4 py-3 text-right space-x-2">
              {{-- View Items scoped by both category & item-category --}}
              <a href="{{ route('store.categories.items', ['category' => $r['category_id'], 'ic' => $itemCategoryId]) }}"
                 class="text-blue-700 hover:underline text-sm">
                View Items
              </a>
              <span class="mx-1 text-gray-300">|</span>
              {{-- Ledger by Product Category (not item-category scoped) --}}
              <a href="{{ route('store.categories.show', $r['category_id']) }}"
                 class="text-indigo-600 hover:underline text-sm">
                View Ledger
              </a>
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">No product categories under this item category.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
