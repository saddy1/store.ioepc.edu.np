@extends('Backend.layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
  <div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold">Edit Purchase #{{ $purchase->purchase_sn }}</h1>
    <a href="{{ route('purchases.show', $purchase) }}" class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">← Back</a>
  </div>

  @if ($errors->any())
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
      <ul class="list-disc list-inside text-sm">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden" x-data="editForm()">
    <div class="border-b bg-gray-50 px-5 py-4"><h2 class="text-sm font-semibold text-gray-700">Purchase Details</h2></div>

    <div class="px-5 py-6">
      <form method="POST" action="{{ route('purchases.update', $purchase) }}" class="space-y-6">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
          <div>
            <label class="block text-sm font-medium mb-1">Purchase S.N *</label>
            <input name="purchase_sn" value="{{ old('purchase_sn', $purchase->purchase_sn) }}" required class="w-full rounded-lg border px-3 py-2">
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Purchase Date *</label>
            <input type="date" name="purchase_date" value="{{ old('purchase_date', optional($purchase->purchase_date)->toDateString()) }}" required class="w-full rounded-lg border px-3 py-2">
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Supplier *</label>
            <select name="supplier_id" required class="w-full rounded-lg border px-3 py-2">
              <option value="">-- Select Supplier --</option>
              @foreach($suppliers as $s)
                <option value="{{ $s->id }}" @selected(old('supplier_id', $purchase->supplier_id)==$s->id)>{{ $s->name }} {{ $s->pan ? "({$s->pan})" : '' }}</option>
              @endforeach
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Department</label>
            <select name="department_id" class="w-full rounded-lg border px-3 py-2">
              <option value="">-- Select Department --</option>
              @foreach($departments as $d)
                <option value="{{ $d->id }}" @selected(old('department_id', $purchase->department_id)==$d->id)>{{ $d->name }}</option>
              @endforeach
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Store Entry S.N</label>
            <input name="store_entry_sn" value="{{ old('store_entry_sn', $purchase->store_entry_sn) }}" class="w-full rounded-lg border px-3 py-2">
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Store Entry Date</label>
            <input type="date" name="store_entry_date" value="{{ old('store_entry_date', optional($purchase->store_entry_date)->toDateString()) }}" class="w-full rounded-lg border px-3 py-2">
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Tax Mode</label>
            <select name="tax_mode" id="tax_mode" class="w-full rounded-lg border px-3 py-2">
              @php $tm = old('tax_mode', $purchase->tax_mode ?? 'PAN'); @endphp
              <option value="PAN" @selected($tm==='PAN')>PAN (No VAT)</option>
              <option value="VAT" @selected($tm==='VAT')>VAT</option>
            </select>
          </div>

          <div id="vat_box" style="{{ $tm==='VAT' ? '' : 'display:none' }}">
            <label class="block text-sm font-medium mb-1">VAT %</label>
            <input type="number" step="0.01" min="0" max="100" name="vat_percent"
                   value="{{ old('vat_percent', $purchase->vat_percent ?? 13) }}"
                   class="w-full rounded-lg border px-3 py-2">
          </div>
        </div>

        <div class="pt-2">
          <label class="block text-sm font-semibold mb-2">Items</label>
          <div id="itemsWrap" class="space-y-2">
            @foreach($purchase->slip->items as $i => $it)
              @php
                // Remaining excluding this purchase (controller also validates)
                $ordered = (float)$it->ordered_qty;
                $purchasedOther = \App\Models\PurchaseItem::where('product_id',$it->product_id)
                    ->whereHas('purchase', fn($q)=>$q->where('purchase_slip_id',$purchase->slip->id)->where('id','!=',$purchase->id))
                    ->sum('qty');
                $remaining = max(0, $ordered - (float)$purchasedOther);
                $existingLine = $purchase->items->firstWhere('product_id', $it->product_id);
              @endphp

              <div class="grid grid-cols-12 gap-2 items-center item-row" x-data="{qty: {{ (float)($existingLine->qty ?? 0) }}, rate: {{ (float)($existingLine->rate ?? 0) }}}">
                <div class="col-span-4">
                  <input type="hidden" name="items[{{ $i }}][product_id]" value="{{ $it->product_id }}">
                  <div class="text-sm font-medium text-gray-900">
                    {{ $it->product->name ?? 'Product #'.$it->product_id }}
                    <span class="text-xs text-gray-500">{{ $it->product?->sku ? '('.$it->product->sku.')' : '' }}</span>
                  </div>
                  <div class="text-[11px] text-gray-500">Unit: {{ $it->unit ?? $it->product->unit ?? '—' }}</div>
                </div>

                <div class="col-span-3">
                  <input name="items[{{ $i }}][qty]" type="number" step="0.001" min="0.001" max="{{ $remaining + (float)($existingLine->qty ?? 0) }}"
                         @input="qty = +$event.target.value"
                         value="{{ old("items.$i.qty", $existingLine->qty ?? '') }}"
                         class="w-full rounded-lg border px-3 py-2" placeholder="Qty ≤ {{ $remaining + (float)($existingLine->qty ?? 0) }}" required>
                  <div class="text-[11px] text-gray-500 mt-1">Remaining (with current): {{ number_format($remaining + (float)($existingLine->qty ?? 0),3) }}</div>
                </div>

                <div class="col-span-3">
                  <input name="items[{{ $i }}][rate]" type="number" step="0.01" min="0" max="{{ $it->max_rate }}"
                         @input="rate = +$event.target.value"
                         value="{{ old("items.$i.rate", $existingLine->rate ?? '') }}"
                         class="w-full rounded-lg border px-3 py-2" placeholder="Rate ≤ {{ $it->max_rate }}" required>
                  <div class="text-[11px] text-gray-500 mt-1">Max rate: {{ number_format($it->max_rate,2) }}</div>
                </div>

                <div class="col-span-2">
                  <input type="text" :value="(qty*rate).toFixed(2)" class="w-full rounded-lg border px-3 py-2 bg-gray-50" readonly>
                </div>
              </div>
            @endforeach
          </div>
        </div>

        <div class="pt-4 flex flex-col items-end gap-1 text-sm" id="summaryBox">
          <div>Sub Total: <span id="js-subtotal" class="font-semibold">{{ number_format($purchase->sub_total,2) }}</span></div>
          <div>VAT: <span id="js-vat" class="font-semibold">{{ number_format($purchase->vat_amount,2) }}</span></div>
          <div class="text-lg">Grand Total: <span id="js-grand" class="font-bold">{{ number_format($purchase->grand_total ?: $purchase->total_amount,2) }}</span></div>
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Remarks</label>
          <textarea name="remarks" rows="2" class="w-full rounded-lg border border-gray-300 focus:border-gray-400 focus:ring-0 px-3 py-2">{{ old('remarks', $purchase->remarks) }}</textarea>
        </div>

        <div class="flex justify-end">
          <button class="rounded-xl bg-gray-900 text-white px-5 py-2.5 text-sm font-semibold hover:bg-gray-800">Update Purchase</button>
        </div>
      </form>
    </div>
  </div>
</div>

@push('scripts')
<script>
function editForm(){ return {} }

document.getElementById('tax_mode').addEventListener('change', (e)=>{
  document.getElementById('vat_box').style.display = e.target.value==='VAT' ? '' : 'none';
  recalcTotals();
});
document.addEventListener('input', (e)=>{
  if (!e.target.form) return;
  if (/\[qty\]$/.test(e.target.name) || /\[rate\]$/.test(e.target.name) || e.target.name==='vat_percent') {
    recalcTotals();
  }
});
function recalcTotals(){
  let sub = 0;
  document.querySelectorAll('#itemsWrap .item-row').forEach(r=>{
    const q = parseFloat(r.querySelector('[name$=\"[qty]\"]')?.value||0);
    const rt= parseFloat(r.querySelector('[name$=\"[rate]\"]')?.value||0);
    sub += (q*rt)||0;
  });
  document.getElementById('js-subtotal').innerText = sub.toFixed(2);
  const mode = document.getElementById('tax_mode').value;
  const vpEl = document.querySelector('[name=vat_percent]');
  const v = mode==='VAT' ? parseFloat(vpEl?.value||13) : 0;
  const vat = +(sub * (v/100)).toFixed(2);
  document.getElementById('js-vat').innerText = vat.toFixed(2);
  document.getElementById('js-grand').innerText = (sub+vat).toFixed(2);
}
recalcTotals();
</script>
@endpush
@endsection
