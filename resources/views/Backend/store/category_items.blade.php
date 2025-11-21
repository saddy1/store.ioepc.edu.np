@extends('Backend.layouts.app')
@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
  <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
      <h1 class="text-2xl font-bold">Items — {{ $categoryName }}</h1>
      <div class="text-sm text-gray-500">
        <a href="{{ route('store.ledger') }}" class="text-blue-600 hover:underline">← All Categories</a>
      </div>
    </div>
    <div class="flex gap-2">
      <a href="{{ route('store.categories.show', $categoryId) }}"
         class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
        View Ledger
      </a>
    </div>
  </div>

  <form method="GET" action="{{ route('store.categories.items', $categoryId) }}" class="mb-6">
    <div class="flex flex-col sm:flex-row gap-3 sm:items-center">
      <input type="text" name="search" value="{{ $filters['search'] }}"
             class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400"
             placeholder="Search item name / SN / supplier / purchase SN / slip SN...">

      <input type="date" name="from" value="{{ $filters['from'] }}"
             class="rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
      <input type="date" name="to"   value="{{ $filters['to']   }}"
             class="rounded-xl border border-gray-300 px-4 py-2.5 text-sm">

      <button class="rounded-xl bg-blue-600 text-white px-5 py-2.5 text-sm hover:bg-blue-700">Search</button>

      @if($filters['search'] || $filters['from'] || $filters['to'])
        <a href="{{ route('store.categories.items', $categoryId) }}"
           class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
          Reset
        </a>
      @endif
    </div>
  </form>

  <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
    <table class="w-full text-left">
      <thead class="bg-gray-50 text-xs uppercase text-gray-600">
        <tr>
          <th class="px-4 py-3 w-16">S.N</th>
          <th class="px-4 py-3">Name (SN)</th>
          <th class="px-4 py-3">Supplier</th>
          <th class="px-4 py-3">Slip</th>
          <th class="px-4 py-3">Purchase</th>
          <th class="px-4 py-3">Unit</th>
          <th class="px-4 py-3">Qty</th>
          <th class="px-4 py-3">Rate</th>
          <th class="px-4 py-3">Amount</th>
          <th class="px-4 py-3 text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        @php $sn = ($items->currentPage() - 1) * $items->perPage() + 1; @endphp
        @forelse($items as $it)
          @php
            $p   = $it->entry?->purchase;
            $sl  = $p?->slip;
            $sup = $p?->supplier?->name;
            $qty = (float)($it->qty ?? 0);
            $rate= (float)($it->rate ?? 0);
            $amt = (float)($it->total_price ?? ($qty * $rate));
          @endphp
          <tr class="hover:bg-gray-50">
            <td class="px-4 py-3">{{ $sn++ }}</td>

            <td class="px-4 py-3">
              <div class="font-medium text-gray-900">{{ $it->item_name }}</div>
              <div class="text-xs text-gray-500">{{ $it->item_sn }}</div>
            </td>

            <td class="px-4 py-3">{{ $sup ?? '—' }}</td>

            <td class="px-4 py-3 text-sm">
              @if($sl)
                <div>
                  <a class="text-blue-600 hover:underline"
                     href="{{ route('slips.show', $sl->id ?? $p->purchase_slip_id) }}">
                    {{ $sl->po_sn }}
                  </a>
                </div>
                <div class="text-xs text-gray-500">{{ optional($sl->po_date)->format('Y-m-d') }}</div>
              @else
                —
              @endif
            </td>

            <td class="px-4 py-3 text-sm">
              @if($p)
                <div>
                  <a class="text-blue-600 hover:underline"
                     href="{{ route('purchases.show', $p->id) }}">
                    {{ $p->purchase_sn }}
                  </a>
                </div>
                <div class="text-xs text-gray-500">{{ optional($p->purchase_date)->format('Y-m-d') }}</div>
              @else
                —
              @endif
            </td>

            <td class="px-4 py-3">{{ $it->unit ?: '—' }}</td>
            <td class="px-4 py-3">{{ number_format($qty, 3) }}</td>
            <td class="px-4 py-3">{{ number_format($rate, 2) }}</td>
            <td class="px-4 py-3">{{ number_format($amt, 2) }}</td>

            <td class="px-4 py-3 text-right space-x-2">
              {{-- Go to Ledger for this category --}}
              <a href="{{ route('store.categories.show', $categoryId) }}" class="text-indigo-600 hover:underline text-sm">
                See Ledger
              </a>
              {{-- Optional: view the Store Entry that contains this item --}}
              @if($it->entry)
                <span class="mx-1 text-gray-300">|</span>
                <a href="{{ route('store.show', $it->entry) }}" class="text-blue-600 hover:underline text-sm">
                  Store Entry
                </a>
                <a href="{{ route('store.out.create', ['item_id' => $it->id]) }}"
     class="text-emerald-600 hover:underline text-sm">
    Store OUT
  </a>
              @endif
            </td>
            
          </tr>
        @empty
          <tr>
            <td colspan="10" class="px-4 py-6 text-center text-gray-500">No items found in this category.</td>
          </tr>
        @endforelse
      </tbody>
    </table>

    <div class="px-4 py-3 border-t bg-gray-50">
      {{ $items->links() }}
    </div>
  </div>
</div>
@endsection
