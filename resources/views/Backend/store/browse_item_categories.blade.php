@extends('Backend.layouts.app')
@section('content')
<div class="max-w-5xl mx-auto px-4 py-8">
  <div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold">Item Categories in Store</h1>
    <a href="{{ route('store.browse') }}"
       class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
      ← Browse
    </a>
  </div>

  <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
    <table class="w-full text-left">
      <thead class="bg-gray-50 text-xs uppercase text-gray-600">
        <tr>
          <th class="px-4 py-3 w-16">S.N</th>
          <th class="px-4 py-3">Item Category</th>
          <th class="px-4 py-3">Items</th>
          <th class="px-4 py-3">Amount</th>
          <th class="px-4 py-3 text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        @php $sn = 1; @endphp
        @forelse($ics as $r)
          <tr class="hover:bg-gray-50">
            <td class="px-4 py-3">{{ $sn++ }}</td>
            <td class="px-4 py-3 font-medium">{{ $r['name'] }}</td>
            <td class="px-4 py-3">{{ $r['items_count'] }}</td>
            <td class="px-4 py-3">{{ $r['total_amount'] }}</td>
            <td class="px-4 py-3 text-right">
              <a href="{{ route('store.browse.ic.categories', $r['item_category_id']) }}"
                 class="text-blue-600 hover:underline text-sm">
                Choose Product Category →
              </a>
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">No item categories found in store.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
