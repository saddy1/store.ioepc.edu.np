@extends('Backend.layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
  <div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold">माग फारम सम्पादन गर्नुहोस्</h1>
    <div class="flex gap-2">
      <a href="{{ route('slips.show', $slip) }}"
         class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">View</a>
      <a href="{{ route('slips.index') }}"
         class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">← Back</a>
    </div>
  </div>

  @if ($errors->any())
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
      <ul class="list-disc list-inside text-sm">
        @foreach ($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden" 
       x-data="slipEditForm()" x-init="init()">
    <div class="border-b bg-gray-50 px-5 py-4">
      <h2 class="text-sm font-semibold text-gray-700">Slip Information</h2>
    </div>

    <div class="px-5 py-6">
      <form method="POST" action="{{ route('slips.update', $slip) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">माग फारम नं *</label>
            <input name="po_sn" value="{{ old('po_sn', $slip->po_sn) }}" readonly required
                   class="w-full rounded-lg border border-gray-300 focus:border-gray-400 focus:ring-0 px-3 py-2">
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">माग गरिएको मिति (BS)</label>
            <input type="text" name="po_date" value="{{ old('po_date', optional($slip->po_date)->format('Y-m-d')) }}"
                   class="w-full rounded-lg border border-gray-300 focus:border-gray-400 focus:ring-0 px-3 py-2 date-mask"
                   placeholder="YYYY-MM-DD" inputmode="numeric" maxlength="10" pattern="\d{4}-\d{2}-\d{2}"
                   autocomplete="off" title="Use format YYYY-MM-DD">
          </div>

          <div class="sm:col-span-2">
            <label class="block text-sm font-medium mb-1">माग गर्ने बिभाग *</label>
            <select name="department_id" required
                    class="w-full rounded-lg border border-gray-300 focus:border-gray-400 focus:ring-0 px-3 py-2">
              <option value="">-- Select Department --</option>
              @foreach ($departments as $d)
                <option value="{{ $d->id }}" @selected(old('department_id', $slip->department_id) == $d->id)>
                  {{ $d->name }}
                </option>
              @endforeach
            </select>
          </div>
        </div>

        {{-- ITEMS --}}
        <div>
          <label class="block text-sm font-semibold mb-2">माग गरेको सामग्री *</label>

          {{-- Row template --}}
          <template id="row-template">
            <div class="grid grid-cols-12 gap-2 items-start row">
              {{-- Name + Suggestions + Detail --}}
              <div class="col-span-4 space-y-2">
                <label class="block text-xs text-gray-600">Item (search previous slips or type custom)</label>

                <input type="text" class="w-full rounded-lg border px-3 py-2 product-search"
                       placeholder="Search by name or SN…">

                <div class="suggestions hidden border rounded-lg bg-white shadow z-10 max-h-48 overflow-auto"></div>

                {{-- hidden fields posted to server --}}
                <input type="hidden" class="product-id-field">
                <input type="hidden" class="temp-name-field">
                <input type="hidden" class="temp-sn-field">

                {{-- Visible Detail input (maps to temp_sn via hidden field) --}}
                <input type="text" class="w-full rounded-lg border px-3 py-2 detail-input"
                       placeholder="Detail (e.g., model/spec)">
              </div>

              {{-- Qty --}}
              <div class="col-span-1">
                <label class="block text-xs text-gray-600">Quantity *</label>
                <input type="number" step="0.001" min="0.001" placeholder="Qty"
                       class="w-full rounded-lg border px-3 py-2 ordered-qty" required>
              </div>

              {{-- Max Rate --}}
              <div class="col-span-2">
                <label class="block text-xs text-gray-600">Max Rate *</label>
                <input type="number" step="0.01" min="0" placeholder="Max Rate"
                       class="w-full rounded-lg border px-3 py-2 max-rate" required>
              </div>

              {{-- Unit --}}
              <div class="col-span-1">
                <label class="block text-xs text-gray-600">Unit</label>
                <input type="text" placeholder="e.g., pcs"
                       class="w-full rounded-lg border px-3 py-2 unit">
              </div>

              {{-- Item Category --}}
              <div class="col-span-3">
                <label class="block text-sm font-medium mb-1">Item Category *</label>
                <select class="w-full rounded-lg border border-gray-300 focus:border-gray-400 focus:ring-0 px-3 py-2 item-category" required>
                  <option value="">-- Select Category --</option>
                  @foreach ($item_category as $c)
                    <option value="{{ $c->id }}">{{ $c->name_en }} ({{ $c->name_np }})</option>
                  @endforeach
                </select>
              </div>

              {{-- Delete --}}
              <div class="col-span-1 flex justify-end pt-6">
                <button type="button" class="text-red-600 text-sm hover:underline del-btn">Del</button>
              </div>
            </div>
          </template>

          {{-- Rows wrapper --}}
          <div id="rows" class="space-y-2"></div>

          <div class="mt-3">
            <button type="button"
                    class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm hover:bg-gray-50 cursor-pointer"
                    @click="addRow()">
              + Add Item
            </button>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Remarks</label>
          <textarea name="remarks" rows="2"
                    class="w-full rounded-lg border border-gray-300 focus:border-gray-400 focus:ring-0 px-3 py-2"
                    placeholder="Optional">{{ old('remarks', $slip->remarks) }}</textarea>
        </div>

        <div class="flex justify-end">
          <button class="rounded-xl bg-gray-900 text-white px-5 py-2.5 text-sm font-semibold hover:bg-gray-800">
            Update Slip
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  (function ensureAlpine(){
    if (!window.Alpine) {
      var s = document.createElement('script');
      s.src = "https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js";
      s.defer = true;
      document.head.appendChild(s);
    }
  })();
</script>

{{-- CRITICAL: Pass slip items and old input to JavaScript --}}
<script>
  // Old input after validation failure
  window.__OLD_ITEMS__ = @json(old('items', []));
  
  // Existing slip items for editing
  window.__SLIP_ITEMS__ = @json($slipItemsForJs ?? []);
</script>

<script>
function slipEditForm() {
  return {
    booted: false,

    init() {
      if (this.booted) return;
      this.booted = true;

      const oldItems = Array.isArray(window.__OLD_ITEMS__) ? window.__OLD_ITEMS__ : [];
      const slipItems = Array.isArray(window.__SLIP_ITEMS__) ? window.__SLIP_ITEMS__ : [];

      // Priority: old input (after validation error) > existing slip items > empty row
      if (oldItems.length) {
        oldItems.forEach(row => this.addRow(row));
      } else if (slipItems.length) {
        slipItems.forEach(row => this.addRow(row));
      } else {
        this.addRow();
      }
    },

    addRow(prefill = null) {
      const wrap = document.getElementById('rows');
      const tmpl = document.getElementById('row-template');
      const node = tmpl.content.firstElementChild.cloneNode(true);
      wrap.appendChild(node);
      this.bindRow(node, prefill);
      this.reindex();
    },

    bindRow(row, prefill = null) {
      // delete row
      row.querySelector('.del-btn').addEventListener('click', () => {
        const wrap = document.getElementById('rows');
        if (wrap.querySelectorAll('.row').length <= 1) return;
        row.remove();
        this.reindex();
      });

      // elements
      const searchInput = row.querySelector('.product-search');
      const listBox     = row.querySelector('.suggestions');
      const pidField    = row.querySelector('.product-id-field');
      const tempName    = row.querySelector('.temp-name-field');
      const tempSN      = row.querySelector('.temp-sn-field');
      const detailInput = row.querySelector('.detail-input');

      const qtyInput    = row.querySelector('.ordered-qty');
      const rateInput   = row.querySelector('.max-rate');
      const unitInput   = row.querySelector('.unit');
      const catSelect   = row.querySelector('.item-category');

      // sync visible detail -> hidden temp_sn
      detailInput.addEventListener('input', () => {
        tempSN.value = detailInput.value.trim();
      });

      // typeahead state
      let debounceTimer = null;
      let currentResults = [];
      let activeIndex = -1;
      let aborter = null;

      const hideList = () => { listBox.classList.add('hidden'); activeIndex = -1; };
      const showList = () => { listBox.classList.remove('hidden'); };

      const renderList = () => {
        if (!currentResults.length) { hideList(); return; }
        listBox.innerHTML = currentResults.map((r,i) => `
          <div class="px-3 py-2 hover:bg-gray-50 cursor-pointer ${i===activeIndex ? 'bg-gray-100' : ''}" data-idx="${i}">
            <div class="text-sm font-medium">${r.text}</div>
            <div class="text-xs text-gray-500">
              ${r.last_qty ?? '—'} @ ${r.last_rate ?? '—'}${r.last_unit ? ' ('+r.last_unit+')' : ''}
              ${r.last_category_name ? ' • ' + r.last_category_name : ''}
            </div>
          </div>
        `).join('');
        showList();

        [...listBox.children].forEach(el => {
          el.addEventListener('mousedown', (e) => { e.preventDefault(); choose(parseInt(el.dataset.idx, 10)); });
          el.addEventListener('mouseenter', () => { activeIndex = parseInt(el.dataset.idx, 10); renderList(); });
        });
      };

      const choose = (idx) => {
        const item = currentResults[idx];
        if (!item) return;

        // we store custom fields, not a product id
        pidField.value   = '';
        tempName.value   = (item.temp_name || '').trim();
        tempSN.value     = (item.temp_sn || '').trim();

        searchInput.value = item.text;
        detailInput.value = tempSN.value;

        // auto-fill values (editable)
        if (item.last_unit)  unitInput.value = item.last_unit;
        else                 unitInput.value = 'pcs';

        if (item.last_rate)  rateInput.value = item.last_rate;
        if (item.last_qty)   qtyInput.value  = item.last_qty;

        // preselect category
        if (item.last_category_id && catSelect) {
          catSelect.value = String(item.last_category_id);
        }

        hideList();
        qtyInput.focus();
        qtyInput.select();
      };

      const fetchResults = (term) => {
        if (aborter) aborter.abort();
        aborter = new AbortController();

        if (!term) { currentResults = []; renderList(); return; }

        fetch(`{{ route('products.search') }}?q=${encodeURIComponent(term)}`, {
          signal: aborter.signal,
          headers: { 
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          }
        })
        .then(r => r.ok ? r.json() : [])
        .then(json => {
          if (!Array.isArray(json)) json = [];
          currentResults = json.map(x => ({
            id: x.id,
            text: (x.text || '').toString(),
            temp_name: x.temp_name || '',
            temp_sn: x.temp_sn || '',
            last_qty: x.last_qty ?? null,
            last_rate: x.last_rate ?? null,
            last_unit: x.last_unit || '',
            last_category_id: x.last_category_id ?? null,
            last_category_name: x.last_category_name ?? '',
          }));
          activeIndex = currentResults.length ? 0 : -1;
          renderList();
        })
        .catch(() => { currentResults = []; renderList(); });
      };

      // typing handlers
      searchInput.addEventListener('input', () => {
        pidField.value = '';
        tempName.value = searchInput.value.trim();

        if (debounceTimer) clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => fetchResults(searchInput.value.trim()), 200);
      });

      searchInput.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowDown' && currentResults.length) {
          e.preventDefault(); activeIndex = (activeIndex + 1) % currentResults.length; renderList();
        } else if (e.key === 'ArrowUp' && currentResults.length) {
          e.preventDefault(); activeIndex = (activeIndex - 1 + currentResults.length) % currentResults.length; renderList();
        } else if (e.key === 'Enter') {
          if (currentResults.length && activeIndex >= 0) {
            e.preventDefault(); choose(activeIndex);
          } else {
            pidField.value  = '';
            tempName.value  = searchInput.value.trim();
            tempSN.value    = detailInput.value.trim();
            hideList();
          }
        } else if (e.key === 'Escape') {
          hideList();
        }
      });

      searchInput.addEventListener('blur', () => {
        setTimeout(() => {
          if (!pidField.value && searchInput.value.trim() !== '') {
            tempName.value = searchInput.value.trim();
            tempSN.value   = detailInput.value.trim();
          }
          hideList();
        }, 120);
      });

      // Prefill from old input (after validation error) or existing slip items
      if (prefill && typeof prefill === 'object') {
        if (prefill.product_id) {
          pidField.value     = String(prefill.product_id);
          searchInput.value  = `#${prefill.product_id} (selected)`;
          tempName.value     = '';
          tempSN.value       = '';
        } else {
          searchInput.value  = prefill.temp_name ?? '';
          detailInput.value  = prefill.temp_sn ?? '';
          pidField.value     = '';
          tempName.value     = prefill.temp_name ?? '';
          tempSN.value       = prefill.temp_sn ?? '';
        }
        if (prefill.ordered_qty != null) qtyInput.value = prefill.ordered_qty;
        if (prefill.max_rate   != null)  rateInput.value= prefill.max_rate;
        if (prefill.unit)                unitInput.value= prefill.unit;
        if (prefill.item_category_id)    catSelect.value= String(prefill.item_category_id);
      }
    },

    reindex() {
      const wrap = document.getElementById('rows');
      [...wrap.querySelectorAll('.row')].forEach((row, i) => {
        row.querySelector('.product-id-field')?.setAttribute('name', `items[${i}][product_id]`);
        row.querySelector('.temp-name-field')?.setAttribute('name', `items[${i}][temp_name]`);
        row.querySelector('.temp-sn-field')?.setAttribute('name', `items[${i}][temp_sn]`);
        row.querySelector('.ordered-qty')?.setAttribute('name', `items[${i}][ordered_qty]`);
        row.querySelector('.max-rate')?.setAttribute('name', `items[${i}][max_rate]`);
        row.querySelector('.unit')?.setAttribute('name', `items[${i}][unit]`);
        row.querySelector('.item-category')?.setAttribute('name', `items[${i}][item_category_id]`);
      });
    }
  }
}
</script>
@endpush