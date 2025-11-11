@extends('Backend.layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
  <div class="mb-6 flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold">Store Entry #{{ $meta['id'] }}</h1>
      <p class="text-sm text-gray-600">
        Purchase: <b>{{ $meta['purchase_sn'] }}</b> • Date: {{ $meta['purchase_date'] ?? '—' }} •
        Supplier: {{ $meta['supplier'] }}
        @if($meta['slip_sn'])
          • Slip: {{ $meta['slip_sn'] }} ({{ $meta['slip_date'] ?? '—' }})
        @endif
      </p>
    </div>
    <a href="{{ route('store.index') }}"
       class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">← Store Entries</a>
  </div>

  <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
    <table class="w-full text-left">
      <thead class="bg-gray-50 text-xs uppercase text-gray-600">
        <tr>
          <th class="px-4 py-3 w-14">S.N</th>
          <th class="px-4 py-3">Item</th>
          <th class="px-4 py-3">Unit</th>
          <th class="px-4 py-3">Qty</th>
          <th class="px-4 py-3">Rate</th>
          <th class="px-4 py-3">Amount</th>
          <th class="px-4 py-3">Product Category</th>
          <th class="px-4 py-3">Ledger</th>
          <th class="px-4 py-3 text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        @forelse($rows as $r)
          <tr class="hover:bg-gray-50">
            <td class="px-4 py-3">{{ $r['sn'] }}</td>
            <td class="px-4 py-3">
              <div class="font-medium">{{ $r['name'] }}</div>
              <div class="text-xs text-gray-500">{{ $r['sn_code'] }}</div>
            </td>
            <td class="px-4 py-3">{{ $r['unit'] }}</td>
            <td class="px-4 py-3">{{ $r['qty'] }}</td>
            <td class="px-4 py-3">{{ $r['rate'] }}</td>
            <td class="px-4 py-3">{{ $r['amount'] }}</td>
            <td class="px-4 py-3">{{ $r['category'] }}</td>
            <td class="px-4 py-3">{{ $r['ledger'] }}</td>
            <td class="px-4 py-3 text-right">
              @if($r['category_id'])
                <a href="{{ route('store.ledger.category', $r['category_id']) }}"
                   class="inline-flex items-center gap-1 text-indigo-600 hover:underline text-sm"
                   title="See all items in this Product Category ledger">
                  {{-- icon --}}
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="2" d="M4 19.5V6a2 2 0 012-2h8.5M8 7h8M8 11h8M8 15h5M15 3.5L20.5 9"/></svg>
                  See in Ledger
                </a>
              @else
                <span class="text-xs text-gray-400">No category</span>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="9" class="px-4 py-6 text-center text-gray-500">No items in this store entry.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if($meta['remarks'])
    <div class="mt-4 rounded-xl border border-gray-200 bg-white p-4 text-sm">
      <b>Remarks:</b> {{ $meta['remarks'] }}
    </div>
  @endif
</div>
@endsection
