{{-- resources/views/backend/purchases/create.blade.php --}}
@extends('Backend.layouts.app')

@section('content')
@php
  /** @var \App\Models\PurchaseSlip $slip */
  $slipDate = $slip->po_date?->toDateString();
@endphp

<div class="max-w-6xl mx-auto px-4 py-8">
  <div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold">स्टोर प्राप्ति तयार गर्नुहोस </h1>
    <a href="{{ route('purchases.index') }}"
       class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">← स्टोर प्राप्ति</a>
  </div>

  @if ($errors->any())
    <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
      <ul class="list-disc list-inside text-sm">
        @foreach ($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden"
       x-data="purchaseForm(@json($slip), @json(old('tax_mode','PAN')), @json(old('vat_percent', 13)))">
    <div class="border-b bg-gray-50 px-5 py-4">
      <h2 class="text-sm font-semibold text-gray-700">खरिद विवरण </h2>
    </div>

    <div class="px-5 py-6">
      <form method="POST" action="{{ route('purchases.store') }}" class="space-y-6" id="purchase-form">
        @csrf
        <input type="hidden" name="purchase_slip_id" value="{{ $slip->id }}">

        {{-- Header fields --}}
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-5">
          <div>
            <label class="block text-sm font-medium mb-1">खरिद माग फारम नं  *</label>
            <input name="purchase_sn" value="{{ $slip->po_sn }}" required
                   class="w-full rounded-lg border border-gray-300 focus:border-gray-400 focus:ring-0 px-3 py-2" readonly>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">स्टोर प्राप्ति नं  *</label>
            <input name="purchase_sn" value="{{ old('purchase_sn') }}" required
                   class="w-full rounded-lg border border-gray-300 focus:border-gray-400 focus:ring-0 px-3 py-2">
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">स्टोर प्राप्ति मिति * (BS)</label>
            <input
              type="text"
              name="purchase_date"
              value="{{ old('purchase_date') }}"
              class="w-full rounded-lg border border-gray-300 focus:border-gray-400 focus:ring-0 px-3 py-2 date-mask"
              placeholder="YYYY-MM-DD"
              inputmode="numeric"
              maxlength="10"
              pattern="\d{4}-\d{2}-\d{2}"
              autocomplete="off"
              title="Use format YYYY-MM-DD"
              data-slip-date="{{ $slipDate }}"
            >
            <div class="text-[11px] text-gray-500 mt-1">Must be on/after Slip Date: {{ $slipDate }}</div>
          </div>
           <div>
            <label class="block text-sm font-medium mb-1">बिल नं *</label>
            <input name="bill_no" value="{{ old('bill_no')}}" required
                   class="w-full rounded-lg border border-gray-300 focus:border-gray-400 focus:ring-0 px-3 py-2">
          </div>
</div> <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
          <div>
            <label class="block text-sm font-medium mb-1">सप्लायर्स *</label>
            <select name="supplier_id" required class="w-full rounded-lg border border-gray-300 focus:border-gray-400 focus:ring-0 px-3 py-2">
              <option value="">-- Select Supplier --</option>
              @foreach ($suppliers as $s)
                <option value="{{ $s->id }}" @selected(old('supplier_id') == $s->id)>{{ $s->name }} {{ $s->pan ? "({$s->pan})" : '' }}</option>
              @endforeach
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">बिभाग </label>
            <select name="department_id" class="w-full rounded-lg border border-gray-300 focus:border-gray-400 focus:ring-0 px-3 py-2">
              <option value="">-- Select Department --</option>
              @foreach ($departments as $d)
                <option value="{{ $d->id }}" @selected(old('department_id', $slip->department_id) == $d->id)>{{ $d->name }}</option>
              @endforeach
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Tax Mode</label>
            <select name="tax_mode" x-model="taxMode" class="w-full rounded-lg border border-gray-300 focus:border-gray-400 focus:ring-0 px-3 py-2">
              <option value="PAN" @selected(old('tax_mode') === 'PAN')>PAN</option>
              <option value="VAT" @selected(old('tax_mode') === 'VAT')>VAT</option>
            </select>
          </div>

          <div x-show="taxMode==='VAT'">
            <label class="block text-sm font-medium mb-1">VAT %</label>
            <input type="number" step="0.01" min="0" max="100" name="vat_percent"
                   x-model.number="vatPercent" value="{{ old('vat_percent', 13) }}"
                   class="w-full rounded-lg border border-gray-300 focus:border-gray-400 focus:ring-0 px-3 py-2">
          </div>
        </div>

        {{-- ITEMS from Slip with editable name --}}
        <div class="pt-2">
          <label class="block text-sm font-semibold mb-2">
            Items from Purchase Slip: {{ $slip->po_sn }}
          </label>

          <div id="itemsWrap" class="space-y-3">
            @foreach ($slip->items as $i => $it)
              @php
                $purchased = \App\Models\PurchaseItem::where('product_id', $it->product_id)
                    ->whereHas('purchase', fn($q) => $q->where('purchase_slip_id', $slip->id))
                    ->sum('qty');
                $remaining = max(0, (float) $it->ordered_qty - (float) $purchased);
                $displayName = $it->temp_name ?: ($it->product?->name ?? 'Product #'.$it->product_id);
              @endphp

              <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 sm:gap-3 p-3 border rounded-xl item-row"
                   x-data="{ qty: 0, rate: 0 }"
                   x-init="$watch('qty', () => $dispatch('recalc')); $watch('rate', () => $dispatch('recalc'));">
                {{-- Keep linkage to Slip/Product for server checks --}}
                <input type="hidden" name="items[{{ $i }}][product_id]" value="{{ $it->product_id }}">

                {{-- Editable display name --}}
                <div class="sm:col-span-4">
                  <label class="block text-xs text-gray-600 mb-1">Item Name (editable)</label>
                  <input name="items[{{ $i }}][name]" type="text"
                         value="{{ old("items.$i.name", $displayName) }}"
                         class="w-full rounded-lg border border-gray-300 focus:border-gray-400 focus:ring-0 px-3 py-2">
                  <div class="text-[11px] text-gray-500 mt-1">
                    Unit: {{ $it->unit ?? ($it->product->unit ?? '—') }}
                    @if($it->product?->sku)
                      • SKU: {{ $it->product->sku }}
                    @endif
                  </div>
                </div>
                
                <input type="hidden" name="items[{{ $i }}][unit]" value="{{ $it->unit }}">

                {{-- Quantity --}}
                <div class="sm:col-span-3">
                  <label class="block text-xs text-gray-600 mb-1">Quantity *({{ $it->unit }})</label>
                  <input name="items[{{ $i }}][qty]" type="number" step="0.001" min="0.001"
                         max="{{ $remaining }}"
                         @input="qty = +$event.target.value"
                         class="w-full rounded-lg border border-gray-300 focus:border-gray-400 focus:ring-0 px-3 py-2"
                         placeholder="Qty ≤ {{ $remaining }}"
                         {{ $remaining > 0 ? 'required' : 'disabled' }}>
                  <div class="text-[11px] text-gray-500 mt-1">Remaining: {{ number_format($remaining, 3) }}</div>
                </div>

                {{-- Rate --}}
                <div class="sm:col-span-3">
                  <label class="block text-xs text-gray-600 mb-1">Rate *</label>
                  <input name="items[{{ $i }}][rate]" type="number" step="0.01" min="0"
                         max="{{ $it->max_rate }}"
                         @input="rate = +$event.target.value"
                         class="w-full rounded-lg border border-gray-300 focus:border-gray-400 focus:ring-0 px-3 py-2"
                         placeholder="Rate ≤ {{ $it->max_rate }}"
                         {{ $remaining > 0 ? 'required' : 'disabled' }}>
                  <div class="text-[11px] text-gray-500 mt-1">Max rate: {{ number_format($it->max_rate, 2) }}</div>
                </div>

                {{-- Line Total (readonly) --}}
                <div class="sm:col-span-2">
                  <label class="block text-xs text-gray-600 mb-1">Line Total</label>
                  <input type="text" :value="(qty * rate).toFixed(2)"
                         class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2" readonly>
                </div>
              </div>
            @endforeach
          </div>
        </div>

        {{-- Live totals + hidden vat_amount --}}
        <div class="pt-4 flex flex-col items-end gap-1 text-sm" x-data
             @recalc.window="
                let sub = 0;
                document.querySelectorAll('#itemsWrap .item-row').forEach(r=>{
                  const q = parseFloat(r.querySelector('[name$=&quot;[qty]&quot;]')?.value||0);
                  const rt= parseFloat(r.querySelector('[name$=&quot;[rate]&quot;]')?.value||0);
                  sub += (q*rt)||0;
                });

                const subEl = document.getElementById('js-subtotal');
                if (subEl) subEl.innerText = sub.toFixed(2);

                const mode = document.querySelector('[name=tax_mode]')?.value||'PAN';
                const v = mode==='VAT' ? parseFloat(document.querySelector('[name=vat_percent]')?.value||13) : 0;
                const vat = +(sub * (v/100)).toFixed(2);

                const vatEl = document.getElementById('js-vat');
                if (vatEl) vatEl.innerText = vat.toFixed(2);

                const grand = sub + vat;
                const grandEl = document.getElementById('js-grand');
                if (grandEl) grandEl.innerText = grand.toFixed(2);

                const vatField = document.querySelector('[name=vat_amount]');
                if (vatField) vatField.value = vat.toFixed(2);
             ">
          <div>Sub Total: <span id="js-subtotal" class="font-semibold">0.00</span></div>
          <div>VAT Amount: <span id="js-vat" class="font-semibold">0.00</span></div>
          <div class="text-lg">Grand Total: <span id="js-grand" class="font-bold">0.00</span></div>

          {{-- Client mirrors live VAT for convenience; server is authoritative --}}
          <input type="hidden" name="vat_amount" value="0.00">
        </div>

        {{-- Remarks --}}
        <div>
          <label class="block text-sm font-medium mb-1">Remarks</label>
          <textarea name="remarks" rows="2"
                    class="w-full rounded-lg border border-gray-300 focus:border-gray-400 focus:ring-0 px-3 py-2"
                    placeholder="Optional">{{ old('remarks') }}</textarea>
        </div>

        {{-- Submit --}}
        <div class="flex justify-end">
          <button class="rounded-xl bg-gray-900 text-white px-5 py-2.5 text-sm font-semibold hover:bg-gray-800">
            Save Purchase
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
  {{-- Ensure Alpine is present (safe no-op if already loaded) --}}
  <script>
    (function ensureAlpine() {
      if (!window.Alpine) {
        var s = document.createElement('script');
        s.src = "https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js";
        s.defer = true;
        document.head.appendChild(s);
      }
    })();
  </script>

  <script>
    function purchaseForm(slip, initialTaxMode = 'PAN', initialVat = 13) {
      return {
        slip,
        taxMode: initialTaxMode,
        vatPercent: initialVat
      };
    }

    // Trigger recalc on changes
    document.addEventListener('change', (e) => {
      if (e.target && (e.target.name === 'tax_mode' || e.target.name === 'vat_percent')) {
        window.dispatchEvent(new Event('recalc'));
      }
    });
    document.addEventListener('input', (e) => {
      if (e.target && (/\[qty\]$/.test(e.target.name) || /\[rate\]$/.test(e.target.name))) {
        window.dispatchEvent(new Event('recalc'));
      }
    });

    // Client date guard: purchase_date >= slip_date
    (function () {
      const pd = document.querySelector('[name="purchase_date"]');
      if (!pd) return;
      const slipDate = pd.getAttribute('data-slip-date') || '';

      const warn = (msg) => {
        let hint = pd.parentElement.querySelector('.date-warn');
        if (!hint) {
          hint = document.createElement('div');
          hint.className = 'date-warn text-xs text-red-600 mt-1';
          pd.parentElement.appendChild(hint);
        }
        hint.textContent = msg;
      };
      const clearWarn = () => {
        const hint = pd.parentElement.querySelector('.date-warn');
        if (hint) hint.remove();
      };

      const validYMD = (s) => /^\d{4}-\d{2}-\d{2}$/.test(s);

      const check = () => {
        const v = (pd.value || '').trim();
        if (!validYMD(v) || !validYMD(slipDate)) { clearWarn(); return true; }
        if (v < slipDate) { warn(`Purchase Date cannot be earlier than Slip Date (${slipDate}).`); return false; }
        clearWarn(); return true;
      };

      pd.addEventListener('blur', check);

      const form = document.getElementById('purchase-form');
      if (form) {
        form.addEventListener('submit', (e) => {
          if (!check()) { e.preventDefault(); pd.focus(); }
        });
      }
    })();

    // Kick an initial totals compute
    window.dispatchEvent(new Event('recalc'));
  </script>
@endpush
