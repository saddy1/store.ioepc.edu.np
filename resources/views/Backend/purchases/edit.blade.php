@extends('Backend.layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
  <div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold">Edit Purchase #{{ $purchase->purchase_sn }}</h1>
    <a href="{{ route('purchases.index') }}"
       class="rounded-xl border px-4 py-2 text-sm bg-white">← Back</a>
  </div>

  @if ($errors->any())
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3">
      <ul class="list-disc list-inside text-sm text-red-700">
        @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
      </ul>
    </div>
  @endif

  <div class="rounded-2xl border bg-white shadow-sm">
    <div class="border-b bg-gray-50 px-5 py-4">
      <h2 class="text-sm font-semibold text-gray-700">Purchase Details</h2>
    </div>

    <div class="px-5 py-6">
      <form method="POST" action="{{ route('purchases.update', $purchase) }}" class="space-y-6">
        @csrf @method('PUT')

        {{-- HEADER --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
          <div>
            <label class="text-sm font-medium">Purchase SN *</label>
            <input name="purchase_sn" value="{{ old('purchase_sn',$purchase->purchase_sn) }}"
                   class="w-full rounded-lg border px-3 py-2" required>
          </div>

          <div>
            <label class="text-sm font-medium">Purchase Date *</label>
            <input type="date" name="purchase_date"
                   value="{{ old('purchase_date', optional($purchase->purchase_date)->toDateString()) }}"
                   class="w-full rounded-lg border px-3 py-2" required>
          </div>

          <div>
            <label class="text-sm font-medium">Supplier</label>
            <select name="supplier_id" class="w-full rounded-lg border px-3 py-2">
              <option value="">-- Select Supplier --</option>
              @foreach($suppliers as $s)
                <option value="{{ $s->id }}" @selected($purchase->supplier_id==$s->id)>
                  {{ $s->name }}
                </option>
              @endforeach
            </select>
          </div>
        </div>

        {{-- ITEMS --}}
        <div>
          <h3 class="text-sm font-semibold mb-2">Items</h3>

          <div id="itemsWrap" class="space-y-3">
            @foreach($purchase->items as $i => $item)
              <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 p-3 border rounded-xl item-row">

                {{-- preserve slip reference --}}
                <input type="hidden" name="items[{{ $i }}][purchase_slip_item_id]"
                       value="{{ $item->purchase_slip_item_id }}">

                {{-- Source --}}
                <div class="sm:col-span-2">
                  <label class="text-xs text-gray-600">Source</label>
                  <div class="text-xs px-2 py-2 bg-gray-50 rounded border">
                    @if($item->purchase_slip_item_id)
                      Slip Item
                    @else
                      Tender / Quotation
                    @endif
                  </div>
                </div>

                {{-- NAME --}}
                <div class="sm:col-span-4">
                  <label class="text-xs text-gray-600">Item Name *</label>
                  <input name="items[{{ $i }}][name]"
                         value="{{ old("items.$i.name", $item->temp_name) }}"
                         class="w-full rounded-lg border px-3 py-2" required>
                </div>

                {{-- UNIT --}}
                <div class="sm:col-span-2">
                  <label class="text-xs text-gray-600">Unit</label>
                  <input name="items[{{ $i }}][unit]"
                         value="{{ old("items.$i.unit", $item->unit) }}"
                         class="w-full rounded-lg border px-3 py-2">
                </div>

                {{-- QTY --}}
                <div class="sm:col-span-2">
                  <label class="text-xs text-gray-600">Qty *</label>
                  <input name="items[{{ $i }}][qty]" type="number" step="0.001"
                         value="{{ old("items.$i.qty", $item->qty) }}"
                         class="w-full rounded-lg border px-3 py-2" required>
                </div>

                {{-- RATE --}}
                <div class="sm:col-span-2">
                  <label class="text-xs text-gray-600">Rate *</label>
                  <input name="items[{{ $i }}][rate]" type="number" step="0.01"
                         value="{{ old("items.$i.rate", $item->rate) }}"
                         class="w-full rounded-lg border px-3 py-2" required>
                </div>
              </div>
            @endforeach
          </div>
        </div>

        {{-- TOTALS --}}
        <div class="pt-4 text-sm text-right">
          <div>Sub Total: <b>{{ number_format($purchase->sub_total,2) }}</b></div>
          <div>VAT: <b>{{ number_format($purchase->vat_amount,2) }}</b></div>
          <div class="text-lg">Grand Total:
            <b>{{ number_format($purchase->grand_total ?? $purchase->total_amount,2) }}</b>
          </div>
        </div>

        {{-- REMARKS --}}
        <div>
          <label class="text-sm font-medium">Remarks</label>
          <textarea name="remarks" rows="2"
                    class="w-full rounded-lg border px-3 py-2">{{ old('remarks',$purchase->remarks) }}</textarea>
        </div>

        {{-- SUBMIT --}}
        <div class="flex justify-end">
          <button class="rounded-xl bg-gray-900 text-white px-5 py-2.5">
            Update Purchase
          </button>
        </div>

      </form>
    </div>
  </div>
</div>
@endsection
