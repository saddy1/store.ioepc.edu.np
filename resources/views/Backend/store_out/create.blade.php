@extends('Backend.layouts.app')

@section('content')
@php
  $prefill = null;
  if (!empty($prefillItem)) {
    $prefill = [
      'store_entry_item_id' => $prefillItem->id,
      'item_name'           => $prefillItem->item_name,
      'item_sn'             => $prefillItem->item_sn,
      'unit'                => $prefillItem->unit,
      'item_category_id'    => $prefillItem->item_category_id,
      'item_category_name'  => $prefillItem->itemCategory?->name_en,
      'type'                => $prefillItem->itemCategory?->typeLabel() ?? 'Consumable',
    ];
  }
@endphp

<div class="max-w-5xl mx-auto px-4 py-8"
     x-data="storeOutForm()"
     x-init='init(@json($prefill))'>

  <div class="mb-6 flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold text-slate-900">Store OUT</h1>
      <p class="text-xs text-slate-500 mt-1">Consumable decreases stock. Non-consumable requires return before re-issue.</p>
    </div>
    <a href="{{ route('store.out.index') }}"
       class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
      ← Back
    </a>
  </div>

  @if ($errors->any())
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
      <div class="font-semibold mb-1">Please fix the errors:</div>
      <ul class="list-disc list-inside text-sm">
        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('store.out.store') }}" class="space-y-6">
    @csrf

    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
      <div>
        <label class="block text-sm font-medium mb-1">Store OUT SN *</label>
        <input type="text" name="store_out_sn" value="{{ old('store_out_sn') }}"
               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Date (BS) *</label>
        <input type="text" name="store_out_date_bs" value="{{ old('store_out_date_bs') }}"
               placeholder="2082-09-12"
               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Department *</label>
        <select name="department_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
          <option value="">-- Select department --</option>
          @foreach(\App\Models\Department::orderBy('name')->get() as $d)
            <option value="{{ $d->id }}" @selected(old('department_id') == $d->id)>{{ $d->name }}</option>
          @endforeach
        </select>
      </div>

      {{-- Employee autocomplete --}}
      <div class="relative">
        <label class="block text-sm font-medium mb-1">Employee *</label>
        <input type="hidden" name="employee_id" x-ref="employeeId" value="{{ old('employee_id') }}" required>

        <input type="text"
               x-ref="employeeSearch"
               x-model="employeeText"
               @input="onEmployeeInput"
               @blur="onEmployeeBlur"
               @keydown.arrow-down.prevent="moveSelection(1)"
               @keydown.arrow-up.prevent="moveSelection(-1)"
               @keydown.enter.prevent="chooseSelection"
               @keydown.escape="employeeResults = []"
               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"
               placeholder="Type name / email / atten no">

        <p x-show="showEmployeeError" x-cloak class="text-xs text-red-600 mt-1">
          Please select an employee from the list.
        </p>

        <div x-show="employeeResults.length" x-cloak
             class="absolute z-20 w-full mt-1 border rounded-lg bg-white shadow-lg max-h-48 overflow-auto text-sm">
          <template x-for="(e, idx) in employeeResults" :key="e.id">
            <div class="px-3 py-2 cursor-pointer"
                 :class="idx === activeIndex ? 'bg-blue-50 text-blue-700' : 'hover:bg-gray-50'"
                 @mousedown.prevent="selectEmployee(idx)">
              <div x-text="e.text"></div>
            </div>
          </template>
        </div>
      </div>
    </div>

    <div>
      <label class="block text-sm font-medium mb-1">Remarks</label>
      <textarea name="remarks" rows="2" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"
                placeholder="Optional">{{ old('remarks') }}</textarea>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-4">
      <div class="flex items-center justify-between mb-3">
        <div>
          <div class="text-sm font-semibold">Items *</div>
          <div class="text-xs text-gray-500">Qty cannot exceed available. Non-consumable cannot be re-issued without return.</div>
        </div>
        <button type="button"
                class="text-xs px-3 py-1.5 rounded-lg border border-gray-300 bg-white hover:bg-gray-50"
                @click="addRow">+ Add Item</button>
      </div>

      <div class="space-y-3">
        <template x-for="(row, index) in items" :key="index">
          <div class="border rounded-xl p-3 bg-gray-50">
            <div class="grid grid-cols-12 gap-2 items-end">

              <div class="col-span-12 md:col-span-4 relative">
                <label class="block text-xs text-gray-600">Search Item *</label>
                <input type="text"
                       x-model="row.searchText"
                       @input="onItemInput(index)"
                       @blur="onItemBlur(index)"
                       class="w-full rounded-lg border border-gray-300 px-2 py-1.5 text-xs"
                       placeholder="Type item name / SN">

                <div x-show="row.searchResults.length" x-cloak
                     class="absolute z-20 w-full mt-1 border rounded-lg bg-white shadow-lg max-h-48 overflow-auto text-xs">
                  <template x-for="(it, idx2) in row.searchResults" :key="it.id">
                    <div class="px-2 py-1.5 cursor-pointer hover:bg-gray-50"
                         @mousedown.prevent="selectItem(index, idx2)">
                      <div x-text="it.text"></div>
                      <div class="text-[11px] text-gray-500">
                        Avl: <b x-text="it.available_qty"></b> <span x-text="it.unit"></span>
                        <span x-show="it.is_non_consumable" class="ml-2 text-indigo-600">(Non-consumable)</span>
                      </div>
                    </div>
                  </template>
                </div>

                <input type="hidden" :name="`items[${index}][store_entry_item_id]`" x-model="row.store_entry_item_id">
              </div>

              <div class="col-span-12 md:col-span-4">
                <label class="block text-xs text-gray-600">Item</label>
                <input type="text" x-model="row.item_name" readonly
                       class="w-full rounded-lg border border-gray-300 px-2 py-1.5 text-xs bg-gray-100">
              </div>

              <div class="col-span-6 md:col-span-2">
                <label class="block text-xs text-gray-600">Unit</label>
                <input type="text" x-model="row.unit" readonly
                       class="w-full rounded-lg border border-gray-300 px-2 py-1.5 text-xs bg-gray-100">
              </div>

              <div class="col-span-6 md:col-span-2">
                <label class="block text-xs text-gray-600">Qty *</label>
                <input type="number" step="0.001" min="0.001"
                       :max="row.available_qty ?? null"
                       :name="`items[${index}][qty]`"
                       x-model="row.qty"
                       @input="enforceQty(index)"
                       class="w-full rounded-lg border border-gray-300 px-2 py-1.5 text-xs" required>
                <div class="text-[11px] text-gray-600 mt-1" x-show="row.available_qty !== null">
                  Available: <b x-text="row.available_qty"></b>
                </div>
                <div class="text-[11px] text-red-600 mt-1" x-show="row.rowError" x-text="row.rowError"></div>
              </div>

              <div class="col-span-12">
                <label class="block text-xs text-gray-600">Item Remarks (optional)</label>
                <input type="text" :name="`items[${index}][remarks]`" x-model="row.item_remarks"
                       class="w-full rounded-lg border border-gray-300 px-2 py-1.5 text-xs"
                       placeholder="This will show in ledger/print remarks for this row">
              </div>

            </div>
          </div>
        </template>
      </div>
    </div>

    <div class="flex justify-end">
      <button type="submit" @click="validateForm"
              class="rounded-xl bg-gray-900 text-white px-5 py-2.5 text-sm font-semibold hover:bg-gray-800">
        Save Store OUT
      </button>
    </div>
  </form>
</div>
@endsection

@push('scripts')
<script>
function storeOutForm() {
  return {
    employeeText: '',
    employeeResults: [],
    activeIndex: -1,
    employeeSearchTimer: null,
    selectedEmployeeId: null,
    showEmployeeError: false,

    items: [],

    blankItem() {
      return {
        store_entry_item_id: '',
        item_name: '',
        item_sn: '',
        unit: '',
        qty: '',
        available_qty: null,
        is_non_consumable: false,
        rowError: '',
        item_remarks: '',

        searchText: '',
        searchResults: [],
        searchTimer: null,
      };
    },

    init(prefill) {
      this.items = [this.blankItem()];
      if (prefill) {
        this.items[0].store_entry_item_id = prefill.store_entry_item_id;
        this.items[0].item_name = prefill.item_name || '';
        this.items[0].unit = prefill.unit || '';
        this.items[0].searchText = prefill.item_name || '';
      }
    },

    addRow(){ this.items.push(this.blankItem()); },

    onItemInput(index){
      const row = this.items[index];
      if (row.searchTimer) clearTimeout(row.searchTimer);
      row.searchTimer = setTimeout(() => this.searchItem(index), 200);
    },

    onItemBlur(index){
      const row = this.items[index];
      setTimeout(() => row.searchResults = [], 200);
    },

    searchItem(index){
      const row = this.items[index];
      const term = row.searchText.trim();
      if (!term) { row.searchResults = []; return; }

      fetch(`{{ route('store.entry-items.search') }}?q=${encodeURIComponent(term)}`, {
        headers: {'Accept':'application/json'}
      })
      .then(r => r.ok ? r.json() : [])
      .then(json => row.searchResults = Array.isArray(json) ? json : [])
      .catch(() => row.searchResults = []);
    },

    selectItem(index, idx2){
      const row = this.items[index];
      const it = row.searchResults[idx2];
      if (!it) return;

      // prevent duplicate
      const already = this.items.some((r, i) => i !== index && String(r.store_entry_item_id) === String(it.id));
      if (already) { row.rowError = 'This item is already selected in another row.'; return; }

      row.store_entry_item_id = it.id;
      row.item_name = it.item_name;
      row.item_sn = it.item_sn;
      row.unit = it.unit;
      row.available_qty = it.available_qty;
      row.is_non_consumable = !!it.is_non_consumable;
      row.rowError = '';
      row.searchText = it.text;
      row.searchResults = [];

      if (row.is_non_consumable && parseFloat(row.available_qty || 0) <= 0) {
        row.rowError = 'This non-consumable item is already assigned. Return it first.';
        row.qty = '';
      }
    },

    enforceQty(index){
      const row = this.items[index];
      if (row.available_qty === null) return;
      const max = parseFloat(row.available_qty);
      const v = parseFloat(row.qty || 0);
      if (v > max) { row.qty = max.toFixed(3); row.rowError = 'Qty cannot be more than available.'; }
      else if (row.rowError === 'Qty cannot be more than available.') row.rowError = '';
    },

    // employee search (keep your existing route)
    onEmployeeInput(){
      if (this.employeeSearchTimer) clearTimeout(this.employeeSearchTimer);
      const term = this.employeeText.trim();
      if (!term) { this.employeeResults = []; return; }

      this.employeeSearchTimer = setTimeout(() => {
        fetch(`{{ route('employees.search') }}?q=${encodeURIComponent(term)}`, {
          headers: {'Accept':'application/json'}
        })
        .then(r => r.ok ? r.json() : [])
        .then(json => this.employeeResults = Array.isArray(json) ? json : [])
        .catch(() => this.employeeResults = []);
      }, 200);
    },

    onEmployeeBlur(){
      setTimeout(() => { this.employeeResults = []; }, 200);
    },

    selectEmployee(idx){
      const e = this.employeeResults[idx];
      if (!e) return;
      this.$refs.employeeId.value = e.id;
      this.selectedEmployeeId = e.id;
      this.employeeText = e.text;
      this.employeeResults = [];
      this.showEmployeeError = false;
    },

    validateForm(e){
      if (!this.$refs.employeeId.value) {
        e.preventDefault();
        this.showEmployeeError = true;
        return;
      }
      for (const row of this.items) {
        if (!row.store_entry_item_id || row.rowError || !(parseFloat(row.qty || 0) > 0)) {
          e.preventDefault();
          alert('Fix item selection / qty errors first.');
          return;
        }
      }
    }
  }
}
</script>
@endpush
