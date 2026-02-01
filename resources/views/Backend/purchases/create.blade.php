{{-- resources/views/backend/purchases/create.blade.php --}}
@extends('Backend.layouts.app')

@section('content')
  <div class="max-w-6xl mx-auto px-4 py-8">
    <div class="mb-6 flex items-center justify-between">
      <h1 class="text-2xl font-bold">स्टोर प्राप्ति तयार गर्नुहोस</h1>
      <a
        href="{{ route('purchases.index') }}"
        class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50"
      >← स्टोर प्राप्ति</a>
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

    <div
      x-data="purchaseForm({
        lookupUrl: '{{ route('order_slips.lookup') }}',
        initialTaxMode: '{{ old('tax_mode', 'PAN') }}',
        initialVat: {{ old('vat_percent', 13) }}
      })"
      x-cloak
    >
      <div class="border-b bg-gray-50 px-5 py-4">
        <h2 class="text-sm font-semibold text-gray-700">खरिद विवरण</h2>
      </div>

      <div class="px-5 py-6">
        <form method="POST" action="{{ route('purchases.store') }}" class="space-y-6" id="purchase-form">
          @csrf

          {{-- Header fields --}}
          <div class="grid grid-cols-1 sm:grid-cols-4 gap-5">
            <div>
              <label class="block text-sm font-medium mb-1">स्टोर प्राप्ति नं *</label>
              <input
                name="purchase_sn"
                value="{{ old('purchase_sn') }}"
                required
                class="w-full rounded-lg border border-gray-300 focus:border-gray-400 focus:ring-0 px-3 py-2"
              >
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
                data-slip-date=""
              >
              <div class="text-[11px] text-gray-500 mt-1">
                If slip added, Purchase Date must be on/after Slip Date.
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium mb-1">बिल नं *</label>
              <input
                name="bill_no"
                value="{{ old('bill_no') }}"
                required
                class="w-full rounded-lg border border-gray-300 focus:border-gray-400 focus:ring-0 px-3 py-2"
              >
            </div>

            <div>
              <label class="block text-sm font-medium mb-1">सप्लायर्स *</label>
              <select
                name="supplier_id"
                required
                class="w-full rounded-lg border border-gray-300 focus:border-gray-400 focus:ring-0 px-3 py-2"
              >
                <option value="">-- Select Supplier --</option>
                @foreach ($suppliers as $s)
                  <option value="{{ $s->id }}" @selected(old('supplier_id') == $s->id)>
                    {{ $s->name }} {{ $s->pan ? "({$s->pan})" : '' }}
                  </option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-4 gap-5">
            <div>
              <label class="block text-sm font-medium mb-1">बिभाग</label>
              <select
                name="department_id"
                class="w-full rounded-lg border border-gray-300 focus:border-gray-400 focus:ring-0 px-3 py-2"
              >
                <option value="">-- Select Department --</option>
                @foreach ($departments as $d)
                  <option value="{{ $d->id }}" @selected(old('department_id') == $d->id)>
                    {{ $d->name }}
                  </option>
                @endforeach
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium mb-1">Tax Mode</label>
              <select
                name="tax_mode"
                x-model="taxMode"
                class="w-full rounded-lg border border-gray-300 focus:border-gray-400 focus:ring-0 px-3 py-2"
              >
                <option value="PAN">PAN</option>
                <option value="VAT">VAT</option>
              </select>
            </div>

            <div x-show="taxMode==='VAT'">
              <label class="block text-sm font-medium mb-1">VAT %</label>
              <input
                type="number"
                step="0.01"
                min="0"
                max="100"
                name="vat_percent"
                x-model.number="vatPercent"
                class="w-full rounded-lg border border-gray-300 focus:border-gray-400 focus:ring-0 px-3 py-2"
              >
            </div>
          </div>

          {{-- ========== ADD ORDER SLIP (PO SN) ========== --}}
          <div class="rounded-xl border border-gray-200 p-4">
            <div class="flex flex-col sm:flex-row gap-3 sm:items-end">
              <div class="flex-1">
                <label class="block text-sm font-semibold mb-1">खरिद माग फाराम नं (PO SN)</label>
                <input
                  x-model="poSn"
                  class="w-full rounded-lg border border-gray-300 focus:border-gray-400 focus:ring-0 px-3 py-2"
                  placeholder="Enter Order Slip No e.g. PO-001"
                >
                <div class="text-[11px] text-gray-500 mt-1">
                  Type slip no → Fetch → select items → Add to purchase
                </div>
              </div>

              <button
                type="button"
                @click="lookupSlip()"
                class="rounded-xl bg-gray-900 text-white px-5 py-2.5 text-sm font-semibold hover:bg-gray-800"
              >
                Fetch Items
              </button>

              <button
                type="button"
                @click="clearSlip()"
                class="rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50"
              >
                Clear
              </button>
            </div>

            <template x-if="lookupError">
              <div class="mt-2 text-sm text-red-600" x-text="lookupError"></div>
            </template>

            <template x-if="slip">
              <div class="mt-4">
                <div class="flex items-center justify-between">
                  <div class="text-sm text-gray-700">
                    <span class="font-semibold">Slip:</span>
                    <span class="font-mono" x-text="slip.po_sn"></span>
                    <span class="mx-2 text-gray-300">|</span>
                    <span class="text-gray-600">Slip Date:</span>
                    <span x-text="slip.po_date"></span>
                  </div>

                  <button
                    type="button"
                    @click="addSelectedSlipItems()"
                    class="rounded-xl bg-emerald-600 text-white px-4 py-2 text-sm font-semibold hover:bg-emerald-700"
                  >
                    + Add Selected
                  </button>
                </div>

                <div class="mt-3 overflow-x-auto border rounded-xl">
                  <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                      <tr>
                        <th class="px-3 py-2 w-10"></th>
                        <th class="px-3 py-2">Item</th>
                        <th class="px-3 py-2">SN</th>
                        <th class="px-3 py-2">Unit</th>
                        <th class="px-3 py-2">Qty</th>
                        <th class="px-3 py-2">Max Rate</th>
                        <th class="px-3 py-2">Status</th>
                      </tr>
                    </thead>
                    <tbody class="divide-y">
                      <template x-for="it in slip.items" :key="it.id">
                        <tr>
                          <td class="px-3 py-2">
                            <input type="checkbox" x-model="it.selected" :disabled="it.is_purchased">
                          </td>
                          <td class="px-3 py-2" x-text="it.name"></td>
                          <td class="px-3 py-2 text-gray-600" x-text="it.sn || '—'"></td>
                          <td class="px-3 py-2 text-gray-600" x-text="it.unit || '—'"></td>
                          <td class="px-3 py-2" x-text="it.ordered_qty"></td>
                          <td class="px-3 py-2" x-text="it.max_rate"></td>
                          <td class="px-3 py-2">
                            <span x-show="it.is_purchased" class="text-red-600 text-xs font-semibold">Purchased</span>
                            <span x-show="!it.is_purchased" class="text-emerald-700 text-xs font-semibold">Available</span>
                          </td>
                        </tr>
                      </template>
                    </tbody>
                  </table>
                </div>

                <div class="text-[11px] text-gray-500 mt-2">
                  Purchased items are locked. Remaining items can be purchased later from another vendor.
                </div>
              </div>
            </template>
          </div>

          {{-- ========== PURCHASE ITEMS CART (Multi-slip + Manual) ========== --}}
          <div class="pt-2">
            <div class="flex items-center justify-between">
              <label class="block text-sm font-semibold">Purchase Items</label>

              <button
                type="button"
                @click="addManualItem()"
                class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
              >
                + Tender/Quotation Item
              </button>
            </div>

            <div id="itemsWrap" class="space-y-3 mt-3">
              <template x-for="(row, i) in cart" :key="row.key">
                <div
                  class="grid grid-cols-1 sm:grid-cols-12 gap-2 sm:gap-3 p-3 border rounded-xl item-row"
                  x-data
                  x-init="
                    $watch(() => row.qty,  () => $dispatch('recalc'));
                    $watch(() => row.rate, () => $dispatch('recalc'));
                    $watch(() => row.name, () => $dispatch('recalc'));
                  "
                >
                  {{-- purchase_slip_item_id (nullable) --}}
                  <input
                    type="hidden"
                    :name="`items[${i}][purchase_slip_item_id]`"
                    :value="row.purchase_slip_item_id || ''"
                  >

                  {{-- Source tag --}}
                  <div class="sm:col-span-2">
                    <label class="block text-xs text-gray-600 mb-1">Source</label>
                    <div class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-xs">
                      <template x-if="row.purchase_slip_item_id">
                        <span class="font-mono text-blue-700" x-text="'Slip #' + row.po_sn"></span>
                      </template>
                      <template x-if="!row.purchase_slip_item_id">
                        <span class="text-gray-600 italic">Tender/Quotation</span>
                      </template>
                    </div>
                  </div>

                  {{-- Item Name --}}
                  <div class="sm:col-span-4">
                    <label class="block text-xs text-gray-600 mb-1">Item Name</label>
                    <input
                      :name="`items[${i}][name]`"
                      type="text"
                      x-model="row.name"
                      :readonl="!!row.purchase_slip_item_id"
                      class="w-full rounded-lg border border-gray-300 focus:border-gray-400 focus:ring-0 px-3 py-2"
                    >
                    <div class="text-[11px] text-gray-500 mt-1">
                      <span x-text="row.unit ? ('Unit: ' + row.unit) : ''"></span>
                      <template x-if="row.max_rate">
                        <span class="ml-2">
                          • Max rate: <span x-text="Number(row.max_rate).toFixed(2)"></span>
                        </span>
                      </template>
                    </div>
                  </div>

                  {{-- Unit --}}
                  <div class="sm:col-span-2">
                    <label class="block text-xs text-gray-600 mb-1">Unit</label>
                    <input
                      :name="`items[${i}][unit]`"
                      type="text"
                      x-model="row.unit"
                      :readonly="!!row.purchase_slip_item_id"
                      class="w-full rounded-lg border border-gray-300 focus:border-gray-400 focus:ring-0 px-3 py-2"
                    >
                  </div>

                  {{-- Qty --}}
                  <div class="sm:col-span-2">
                    <label class="block text-xs text-gray-600 mb-1">Quantity *</label>
                    <input
                      :name="`items[${i}][qty]`"
                      type="number"
                      step="1"
                      min="1"
                      x-model.number="row.qty"
                      :readonl="!!row.purchase_slip_item_id"
                      class="w-full rounded-lg border border-gray-300 focus:border-gray-400 focus:ring-0 px-3 py-2"
                    >
                  </div>

                  {{-- Rate --}}
                  <div class="sm:col-span-2">
                    <label class="block text-xs text-gray-600 mb-1">Rate *</label>
                    <input
                      :name="`items[${i}][rate]`"
                      type="number"
                      step="0.01"
                      min="0"
                      x-model.number="row.rate"
                      @input="enforceMax(row)"
                      class="w-full rounded-lg border border-gray-300 focus:border-gray-400 focus:ring-0 px-3 py-2"
                    >
                  </div>

                  {{-- Line Total --}}
                  <div class="sm:col-span-2">
                    <label class="block text-xs text-gray-600 mb-1">Line Total</label>
                    <input
                      type="text"
                      :value="(Number(row.qty||0) * Number(row.rate||0)).toFixed(2)"
                      class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2"
                      readonly
                    >
                  </div>

                  {{-- Remove --}}
                  <div class="sm:col-span-1 flex items-end justify-end">
                    <button
                      type="button"
                      @click="removeRow(i)"
                      class="text-red-600 hover:underline text-sm font-semibold"
                    >
                      Remove
                    </button>
                  </div>
                </div>
              </template>

              <template x-if="cart.length === 0">
                <div class="text-sm text-gray-500 py-4 text-center border rounded-xl">
                  No items added. Fetch slip items or add Tender/Quotation items.
                </div>
              </template>
            </div>
          </div>

          {{-- Live totals + hidden vat_amount --}}
          <div
            class="pt-4 flex flex-col items-end gap-1 text-sm"
            x-data
            @recalc.window="
              let sub = 0;
              document.querySelectorAll('#itemsWrap .item-row').forEach(r=>{
                const q  = parseFloat(r.querySelector('[name$=&quot;[qty]&quot;]')?.value||0);
                const rt = parseFloat(r.querySelector('[name$=&quot;[rate]&quot;]')?.value||0);
                sub += (q*rt)||0;
              });

              const subEl = document.getElementById('js-subtotal');
              if (subEl) subEl.innerText = sub.toFixed(2);

              const mode = document.querySelector('[name=tax_mode]')?.value||'PAN';
              const v    = mode==='VAT' ? parseFloat(document.querySelector('[name=vat_percent]')?.value||13) : 0;
              const vat  = +(sub * (v/100)).toFixed(2);

              const vatEl = document.getElementById('js-vat');
              if (vatEl) vatEl.innerText = vat.toFixed(2);

              const grand = sub + vat;
              const grandEl = document.getElementById('js-grand');
              if (grandEl) grandEl.innerText = grand.toFixed(2);

              const vatField = document.querySelector('[name=vat_amount]');
              if (vatField) vatField.value = vat.toFixed(2);
            "
          >
            <div>Sub Total: <span id="js-subtotal" class="font-semibold">0.00</span></div>
            <div>VAT Amount: <span id="js-vat" class="font-semibold">0.00</span></div>
            <div class="text-lg">Grand Total: <span id="js-grand" class="font-bold">0.00</span></div>
            <input type="hidden" name="vat_amount" value="0.00">
          </div>

          {{-- Remarks --}}
          <div>
            <label class="block text-sm font-medium mb-1">Remarks</label>
            <textarea
              name="remarks"
              rows="2"
              class="w-full rounded-lg border border-gray-300 focus:border-gray-400 focus:ring-0 px-3 py-2"
              placeholder="Optional"
            >{{ old('remarks') }}</textarea>
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
  <script>
    (function ensureAlpine() {
      if (!window.Alpine) {
        var s = document.createElement('script');
        s.src = 'https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js';
        s.defer = true;
        document.head.appendChild(s);
      }
    })();
  </script>

  <script>
    function purchaseForm({ lookupUrl, initialTaxMode = 'PAN', initialVat = 13 }) {
      return {
        lookupUrl,
        poSn: '',
        slip: null,
        lookupError: '',

        taxMode: initialTaxMode,
        vatPercent: initialVat,

        cart: [],

        async lookupSlip() {
          this.lookupError = '';
          this.slip = null;

          const sn = (this.poSn || '').trim();
          if (!sn) {
            this.lookupError = 'Order Slip No is required.';
            return;
          }

          try {
            const url = new URL(this.lookupUrl, window.location.origin);
            url.searchParams.set('po_sn', sn);

            const res = await fetch(url.toString(), { headers: { Accept: 'application/json' } });
            const data = await res.json();

            if (!data.ok) {
              this.lookupError = data.msg || 'Slip not found.';
              return;
            }

            data.slip.items = (data.slip.items || []).map((i) => ({ ...i, selected: false }));
            this.slip = data.slip;

            this.updateMinSlipDate(this.slip.po_date);
          } catch (e) {
            this.lookupError = 'Failed to fetch slip.';
          }
        },

        clearSlip() {
          this.poSn = '';
          this.slip = null;
          this.lookupError = '';
        },

        addSelectedSlipItems() {
          if (!this.slip) return;

          const selected = this.slip.items.filter((i) => i.selected && !i.is_purchased);
          if (!selected.length) return;

          for (const it of selected) {
            const exists = this.cart.some((x) => x.purchase_slip_item_id === it.id);
            if (exists) {
              it.selected = false;
              continue;
            }

            this.cart.push({
              key: 'slip_' + it.id,
              purchase_slip_item_id: it.id,
              po_sn: this.slip.po_sn,
              name: it.name,
              unit: it.unit || '',
              qty: Number(it.ordered_qty),
              rate: 0,
              max_rate: Number(it.max_rate || 0),
            });

            it.selected = false;
          }

          window.dispatchEvent(new Event('recalc'));
        },

        addManualItem() {
          this.cart.push({
            key: 'manual_' + Math.random().toString(16).slice(2),
            purchase_slip_item_id: null,
            po_sn: null,
            name: '',
            unit: '',
            qty: 1,
            rate: 0,
            max_rate: null,
          });

          window.dispatchEvent(new Event('recalc'));
        },

        removeRow(i) {
          this.cart.splice(i, 1);
          window.dispatchEvent(new Event('recalc'));
        },

        enforceMax(row) {
          if (!row.purchase_slip_item_id) return;
          const max = Number(row.max_rate || 0);
          if (max > 0 && Number(row.rate) > max) row.rate = max;
        },

        updateMinSlipDate(newSlipDate) {
          const pd = document.querySelector('[name="purchase_date"]');
          if (!pd) return;

          const current = (pd.getAttribute('data-slip-date') || '').trim();
          const incoming = (newSlipDate || '').trim();
          if (!incoming) return;

          if (!current || incoming > current) {
            pd.setAttribute('data-slip-date', incoming);
          }
        },
      };
    }

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

    (function () {
      const pd = document.querySelector('[name="purchase_date"]');
      if (!pd) return;

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
        const slipDate = (pd.getAttribute('data-slip-date') || '').trim();
        if (!validYMD(v) || !validYMD(slipDate)) {
          clearWarn();
          return true;
        }
        if (v < slipDate) {
          warn(`Purchase Date cannot be earlier than Slip Date (${slipDate}).`);
          return false;
        }
        clearWarn();
        return true;
      };

      pd.addEventListener('blur', check);

      const form = document.getElementById('purchase-form');
      if (form) {
        form.addEventListener('submit', (e) => {
          if (!check()) {
            e.preventDefault();
            pd.focus();
          }
        });
      }
    })();

    window.dispatchEvent(new Event('recalc'));
  </script>
@endpush
