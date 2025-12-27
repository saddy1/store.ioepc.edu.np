@extends('Backend.layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
  <div class="mb-6 flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold">Store OUT Detail</h1>
      <p class="text-sm text-gray-500">Full details of issued items (employee / department / return status)</p>
    </div>

    <div class="flex gap-2">
      <a href="{{ route('store.out.index') }}"
         class="rounded-xl border px-4 py-2 text-sm bg-white hover:bg-gray-50">← Back</a>
      <a href="{{ route('store.out.print', $storeOut) }}"
         class="rounded-xl border px-4 py-2 text-sm bg-white hover:bg-gray-50">🖨️ Print</a>
    </div>
  </div>

  <div class="rounded-2xl border bg-white p-5 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
      <div>
        <div class="text-gray-500">Store OUT No</div>
        <div class="font-semibold">{{ $storeOut->store_out_sn }}</div>
      </div>
      <div>
        <div class="text-gray-500">Date (BS)</div>
        <div class="font-semibold">{{ $storeOut->store_out_date_bs }}</div>
      </div>
      <div>
        <div class="text-gray-500">Department</div>
        <div class="font-semibold">{{ $storeOut->department?->name ?? '—' }}</div>
      </div>
      <div>
        <div class="text-gray-500">Employee</div>
        <div class="font-semibold">{{ $storeOut->employee?->full_name ?? '—' }}</div>
      </div>
      <div class="md:col-span-4">
        <div class="text-gray-500">Remarks</div>
        <div class="font-semibold">{{ $storeOut->remarks ?? '—' }}</div>
      </div>
    </div>
  </div>

  <div class="rounded-2xl border bg-white overflow-hidden">
    <div class="px-5 py-3 border-b flex justify-between">
      <div class="font-semibold">Issued Items</div>
      <div class="text-sm text-gray-500">Total items: {{ $storeOut->items->count() }}</div>
    </div>

    <table class="w-full text-sm">
      <thead class="bg-gray-50 text-xs uppercase text-gray-600">
        <tr>
          <th class="px-4 py-3 w-12">#</th>
          <th class="px-4 py-3">Item</th>
          <th class="px-4 py-3">SN / Detail</th>
          <th class="px-4 py-3">Category</th>
          <th class="px-4 py-3">Type</th>
          <th class="px-4 py-3 text-right">Qty</th>
          <th class="px-4 py-3">Unit</th>
          <th class="px-4 py-3">Return</th>
          <th class="px-4 py-3">Item Remarks</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @foreach($storeOut->items as $i => $it)
          @php
            $entry = $it->storeEntryItem;
            $cat = $entry?->itemCategory;
            $typeLabel = $cat?->typeLabel() ?? 'Consumable';
          @endphp
          <tr>
            <td class="px-4 py-3">{{ $i+1 }}</td>
            <td class="px-4 py-3 font-medium">{{ $it->item_name ?? $entry?->item_name ?? '—' }}</td>
            <td class="px-4 py-3">{{ $it->item_sn ?? $entry?->item_sn ?? '—' }}</td>
            <td class="px-4 py-3">{{ $cat?->name_en ?? '—' }}</td>
            <td class="px-4 py-3">{{ $typeLabel }}</td>
            <td class="px-4 py-3 text-right">{{ number_format((float)$it->qty, 3) }}</td>
            <td class="px-4 py-3">{{ $it->unit ?? $entry?->unit ?? '—' }}</td>
            <td class="px-4 py-3">
              @if(($cat?->isNonConsumable() ?? false))
                @if($it->returned_at)
                  <span class="text-green-700">Returned</span>
                @else
                  <form method="POST" action="{{ route('store.out.items.return', $it) }}">
                    @csrf
                    <button class="text-blue-600 hover:underline">Mark Returned</button>
                  </form>
                @endif
              @else
                <span class="text-gray-500">N/A</span>
              @endif
            </td>
            <td class="px-4 py-3 text-xs text-gray-600">
              {{ $it->remarks ?? '—' }}
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection
