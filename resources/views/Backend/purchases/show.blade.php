@extends('Backend.layouts.app')
@section('content')
<div class="max-w-5xl mx-auto px-4 py-8">
  <div class="mb-4 flex items-start justify-between">
    <div>
      <h1 class="text-2xl font-bold">Purchase #{{ $purchase->purchase_sn }}</h1>
      <p class="text-sm text-gray-500">
        Date: {{ optional($purchase->purchase_date)->format('Y-m-d') }} •
        Supplier: {{ $purchase->supplier->name ?? '—' }} •
        Tax: {{ $purchase->tax_mode ?? 'PAN' }} {{ $purchase->tax_mode === 'VAT' ? "({$purchase->vat_percent}%)" : '' }}
      </p>
      <p class="text-sm text-gray-500">
        Slip: <a class="text-blue-600 hover:underline" href="{{ route('slips.show', $purchase->purchase_slip_id) }}">{{ $purchase->slip->po_sn ?? $purchase->purchase_slip_id }}</a>
        • Dept: {{ $purchase->department->name ?? $purchase->slip->department->name ?? '—' }}
      </p>
    </div>
    <a href="{{ route('purchases.index') }}" class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">← Back</a>
  </div>

  <div class="rounded-2xl border bg-white shadow-sm overflow-hidden">
    <table class="w-full text-left">
      <thead class="bg-gray-50 text-xs uppercase text-gray-600">
        <tr>
          <th class="px-4 py-3">Product</th>
          <th class="px-4 py-3">Qty</th>
          <th class="px-4 py-3">Rate</th>
          <th class="px-4 py-3">Line Subtotal</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @foreach($purchase->items as $line)
          @php
            $p = $line->product;
            $name = $p?->name ?? ($line->temp_name ?: '—');
            $sku  = $p?->sku  ?? $line->temp_sn;
          @endphp
          <tr>
            <td class="px-4 py-3">
              <div class="font-medium">{{ $name }}</div>
              <div class="text-xs text-gray-500">{{ $sku ? "SKU: $sku" : '' }}</div>
            </td>
            <td class="px-4 py-3">{{ number_format($line->qty,3) }} {{ $line->unit }}</td>
            <td class="px-4 py-3">{{ number_format($line->rate,2) }}</td>
            <td class="px-4 py-3">{{ number_format($line->line_subtotal ?? ($line->qty*$line->rate),2) }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <div class="px-4 py-4 border-t bg-gray-50">
      <div class="flex flex-col items-end gap-1 text-sm">
        <div>Sub Total: <span class="font-semibold">{{ number_format($purchase->sub_total,2) }}</span></div>
        <div>VAT ({{ $purchase->tax_mode === 'VAT' ? $purchase->vat_percent : 0 }}%): <span class="font-semibold">{{ number_format($purchase->vat_amount,2) }}</span></div>
        <div class="text-lg">Grand Total: <span class="font-bold">{{ number_format($purchase->grand_total ?: $purchase->total_amount,2) }}</span></div>
      </div>
    </div>
  </div>
</div>
@endsection
