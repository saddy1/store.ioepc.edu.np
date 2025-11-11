@extends('Backend.layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
  <div class="mb-4 flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold">Slip #{{ $slip->po_sn }}</h1>
      <p class="text-sm text-gray-500">
        Date: {{ $slip->po_date->format('Y-m-d') }}
        • Dept: {{ $slip->department->name ?? '—' }}
      </p>
    </div>
    <a href="{{ route('purchases.create', ['slip_id' => $slip->id]) }}"
       class="rounded-xl bg-gray-900 text-white px-4 py-2.5 text-sm font-semibold hover:bg-gray-800">
      + Create Purchase
    </a>
  </div>

  @if (session('success'))
    <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800 text-sm">
      {{ session('success') }}
    </div>
  @endif
  @if (session('error'))
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800 text-sm">
      {{ session('error') }}
    </div>
  @endif

  <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
    <table class="w-full text-left">
      <thead class="bg-gray-50 text-xs uppercase text-gray-600">
        <tr>
          <th class="px-4 py-3">Item</th>
          <th class="px-4 py-3">Ordered</th>
          <th class="px-4 py-3">Purchased</th>
          <th class="px-4 py-3">Remaining</th>
          <th class="px-4 py-3">Max Rate</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        @forelse($slip->items as $it)
          @php
            // Use precomputed map from controller to avoid N+1; null product_id => 0 purchased
            $purchased = $it->product_id ? (float)($purchasedByProduct[$it->product_id] ?? 0) : 0.0;
            $remaining = max(0, (float)$it->ordered_qty - $purchased);
          @endphp
          <tr class="align-top">
            <td class="px-4 py-3">
              @if($it->product_id && $it->product)
                <div class="font-medium">
                  {{ $it->product->name }}
                  @if($it->product->sku)
                    <span class="text-gray-500">({{ $it->product->sku }})</span>
                  @endif
                </div>
                <div class="mt-1 text-xs inline-flex items-center gap-1 rounded-full bg-blue-50 text-blue-700 px-2 py-0.5">
                  Linked Product
                </div>
              @else
                <div class="font-medium">
                  {{ $it->temp_name ?? '—' }}
                  @if($it->temp_sn)
                    <span class="text-gray-500">({{ $it->temp_sn }})</span>
                  @endif
                </div>
                <div class="mt-1 text-xs inline-flex items-center gap-1 rounded-full bg-amber-50 text-amber-700 px-2 py-0.5">
                  Custom Slip Item
                </div>
              @endif
            </td>

            <td class="px-4 py-3 whitespace-nowrap">
              {{ number_format((float)$it->ordered_qty, 3) }}
              @if($it->unit) <span class="text-gray-500 text-xs">{{ $it->unit }}</span> @endif
            </td>

            <td class="px-4 py-3 whitespace-nowrap">
              {{ number_format($purchased, 3) }}
              @if($it->unit) <span class="text-gray-500 text-xs">{{ $it->unit }}</span> @endif
            </td>

            <td class="px-4 py-3 font-semibold whitespace-nowrap">
              {{ number_format($remaining, 3) }}
              @if($it->unit) <span class="text-gray-500 text-xs">{{ $it->unit }}</span> @endif
            </td>

            <td class="px-4 py-3 whitespace-nowrap">
              {{ number_format((float)$it->max_rate, 2) }}
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="px-4 py-6 text-center text-gray-500">No items on this slip yet.</td>
          </tr>
        @endforelse
      </tbody>

      @if($slip->items->count())
        @php
          $totalOrdered = $slip->items->sum(fn($i) => (float)$i->ordered_qty);
          $totalPurchased = $slip->items->sum(function($i) use ($purchasedByProduct){
            return $i->product_id ? (float)($purchasedByProduct[$i->product_id] ?? 0) : 0.0;
          });
          $totalRemaining = max(0, $totalOrdered - $totalPurchased);
          $grossCap = $slip->items->sum(fn($i) => (float)$i->line_total);
        @endphp
        <tfoot class="bg-gray-50">
          <tr class="font-semibold">
            <td class="px-4 py-3">Totals</td>
            <td class="px-4 py-3">{{ number_format($totalOrdered, 3) }}</td>
            <td class="px-4 py-3">{{ number_format($totalPurchased, 3) }}</td>
            <td class="px-4 py-3">{{ number_format($totalRemaining, 3) }}</td>
            <td class="px-4 py-3">{{ number_format($grossCap, 2) }}</td>
          </tr>
        </tfoot>
      @endif
    </table>
  </div>

  <div class="mt-4 text-xs text-gray-500">
    <p>
      Note: Rows marked <span class="inline-block rounded-full bg-amber-50 text-amber-700 px-2 py-0.5">Custom Slip Item</span>
      were entered as free text on the slip and are not yet linked to a Product. They’ll be linked/created when you make a Purchase.
    </p>
  </div>
</div>
@endsection
