@extends('Backend.layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
  <div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold">Store Entries</h1>
    <a href="{{ route('purchases.index') }}"
       class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
      ← Purchases
    </a>
  </div>

  <form method="GET" action="{{ route('store.index') }}" class="mb-6">
    <div class="flex flex-col sm:flex-row gap-3 sm:items-center">
      <input type="text" name="search" value="{{ request('search') }}"
             class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400"
             placeholder="Search by Purchase SN / Supplier">
      <button class="rounded-xl bg-blue-600 text-white px-5 py-2.5 text-sm hover:bg-blue-700">Search</button>
    </div>
  </form>

  <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
    <table class="w-full text-left">
      <thead class="bg-gray-50 text-xs uppercase text-gray-600">
        <tr>
          <th class="px-4 py-3 w-16">S.N</th>
          <th class="px-4 py-3">Purchase SN / Date</th>
          <th class="px-4 py-3">Supplier</th>
          <th class="px-4 py-3">Items</th>
          <th class="px-4 py-3 text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        @php $sn = ($entries->currentPage()-1)*$entries->perPage()+1; @endphp
        @forelse($entries as $e)
          <tr class="hover:bg-gray-50">
            <td class="px-4 py-3">{{ $sn++ }}</td>
            <td class="px-4 py-3">
              <div class="font-medium">{{ $e->purchase_sn }}</div>
              <div class="text-xs text-gray-500">{{ $e->purchase_date }}</div>
            </td>
            <td class="px-4 py-3">{{ $e->supplier_name ?? $e->supplier?->name ?? '—' }}</td>
            <td class="px-4 py-3">{{ $e->items()->count() }}</td>
            <td class="px-4 py-3 text-right">
              <a class="text-blue-600 hover:underline text-sm" href="{{ route('store.show', $e) }}">View</a>
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">No store entries yet.</td></tr>
        @endforelse
      </tbody>
    </table>
    <div class="px-4 py-3 border-t bg-gray-50">
      {{ $entries->links() }}
    </div>
  </div>
</div>
@endsection
