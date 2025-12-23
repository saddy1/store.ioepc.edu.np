@extends('Backend.layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8"
     x-data="storeOutForm()"
     x-init="init({{ $prefillItem ? json_encode([
        'store_entry_item_id' => $prefillItem->id,
        'item_name'           => $prefillItem->item_name,
        'item_sn'             => $prefillItem->item_sn,
        'unit'                => $prefillItem->unit,
        'item_category_id'    => $prefillItem->item_category_id,
        'item_category_name'  => $prefillItem->itemCategory?->name_en,
     ]) : 'null' }})">

  <div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold">Store OUT</h1>
    <a href="{{ route('store.out.index') }}"
       class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
      ← Back
    </a>
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

  <form method="POST" action="{{ route('store.out.store') }}" class="space-y-6">
    @csrf

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div>
        <label class="block text-sm font-medium mb-1">Store OUT SN *</label>
        <input type="text" name="store_out_sn"
               value="{{ old('store_out_sn') }}"
               class="w-full rounded-lg border px-3 py-2" required>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Date (BS) *</label>
        <input type="text" name="store_out_date_bs"
               value="{{ old('store_out_date_bs') }}"
               placeholder="YYYY-MM-DD"
               class="w-full rounded-lg border px-3 py-2" required>
      </div>

      {{-- Employee autocomplete - selection only (same as your code) --}}
      <div class="sm:col-span-1 relative">
        <label class="block text-sm font-medium mb-1">Employee *</label>
        <input type="hidden" name="employee_id" x-ref="employeeId" required>
        <div class="relative">
          <input type="text"
                 x-ref="employeeSearch"
                 x-model="employeeText"
                 @input="onEmployeeInput"
                 @blur="onEmployeeBlur"
                 @focus="employeeText && !selectedEmployeeId ? searchEmployee() : null"
                 @keydown.arrow-down.prevent="moveSelection(1)"
                 @keydown.arrow-up.prevent="moveSelection(-1)"
                 @keydown.enter.prevent="chooseSelection"
                 @keydown.escape="employeeResults = []"
                 :class="{'border-green-500 bg-green-50': selectedEmployeeId, 'border-gray-300': !selectedEmployeeId}"
                 class="w-full rounded-lg border px-3 py-2 pr-8"
                 placeholder="Type name / email / atten no">
          {{-- Clear button when employee selected --}}
          <button type="button"
                  x-show="selectedEmployeeId"
                  @click="clearEmployee"
                  class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
        <p x-show="showEmployeeError" x-cloak class="text-xs text-red-500 mt-1">
          Please select an employee from the list
        </p>
        <div x-show="employeeResults.length"
             x-cloak
             class="absolute z-10 w-full mt-1 border rounded-lg bg-white shadow-lg max-h-40 overflow-auto text-sm">
          <template x-for="(e, idx) in employeeResults" :key="e.id">
            <div class="px-3 py-2 cursor-pointer"
                 :class="idx === activeIndex ? 'bg-blue-50 text-blue-700' : 'hover:bg-gray-50'"
                 @mousedown.prevent="selectEmployee(idx)">
              <div x-text="e.text"></div>
            </div>
          </template>
        </div>
        <div x-show="showNoResults"
             x-cloak
             class="absolute z-10 w-full mt-1 border rounded-lg bg-white shadow-lg p-3 text-sm text-gray-500">
          No employees found
        </div>
      </div>
    </div>

    {{-- MULTIPLE ITEMS --}}
    {{-- MULTIPLE ITEMS --}}
<div>
  <div class="flex items-center justify-between mb-2">
    <label class="block text-sm font-semibold">Items to issue *</label>
    <button type="button"
            class="text-xs px-3 py-1.5 rounded-lg border border-gray-300 bg-white hover:bg-gray-50"
            @click="addRow">
      + Add Item
    </button>
  </div>

  <div class="space-y-3">
    <template x-for="(row, index) in items" :key="index">
      <div class="border rounded-xl p-3 bg-gray-50">
        <div class="flex justify-between mb-2">
          <div class="text-xs font-semibold text-gray-600">
            Item #<span x-text="index + 1"></span>
          </div>
          <button type="button"
                  class="text-xs text-red-500 hover:text-red-700"
                  @click="removeRow(index)"
                  x-show="items.length > 1">
            Remove
          </button>
        </div>

        <div class="grid grid-cols-12 gap-2 items-end">
          {{-- 🔍 Search from Store Entry --}}
          <div class="col-span-4 relative">
            <label class="block text-xs text-gray-600">Search Item (Store Entry) *</label>
            <input type="text"
                   x-model="row.searchText"
                   @input="onItemInput(index)"
                   @blur="onItemBlur(index)"
                   @keydown.arrow-down.prevent="moveItemSelection(index, 1)"
                   @keydown.arrow-up.prevent="moveItemSelection(index, -1)"
                   @keydown.enter.prevent="chooseItemSelection(index)"
                   @keydown.escape="row.searchResults = []"
                   class="w-full rounded-lg border px-2 py-1.5 text-xs"
                   placeholder="Type item name / SN">
            {{-- Dropdown --}}
            <div x-show="row.searchResults.length"
                 x-cloak
                 class="absolute z-10 w-full mt-1 border rounded-lg bg-white shadow-lg max-h-40 overflow-auto text-xs">
              <template x-for="(it, idx2) in row.searchResults" :key="it.id">
                <div class="px-2 py-1.5 cursor-pointer"
                     :class="idx2 === row.searchActiveIndex ? 'bg-blue-50 text-blue-700' : 'hover:bg-gray-50'"
                     @mousedown.prevent="selectItem(index, idx2)">
                  <div x-text="it.text"></div>
                </div>
              </template>
            </div>
            <div x-show="row.showSearchNoResults"
                 x-cloak
                 class="absolute z-10 w-full mt-1 border rounded-lg bg-white shadow-lg p-2 text-xs text-gray-500">
              No items found
            </div>
          </div>

          {{-- Hidden ID actually submitted --}}
          <input type="hidden"
                 :name="`items[${index}][store_entry_item_id]`"
                 x-model="row.store_entry_item_id">

          {{-- Item Name (locked) --}}
          <div class="col-span-4">
            <label class="block text-xs text-gray-600">Item Name (locked)</label>
            <input type="text"
                   :name="`items[${index}][item_name]`"
                   x-model="row.item_name"
                   class="w-full rounded-lg border px-2 py-1.5 text-xs bg-gray-100 cursor-not-allowed"
                   readonly>
          </div>

          {{-- SN / Detail (locked) --}}
          <div class="col-span-2">
            <label class="block text-xs text-gray-600">Detail (SN)</label>
            <input type="text"
                   :name="`items[${index}][item_sn]`"
                   x-model="row.item_sn"
                   class="w-full rounded-lg border px-2 py-1.5 text-xs bg-gray-100 cursor-not-allowed"
                   readonly>
          </div>

          {{-- Unit (locked) --}}
          <div class="col-span-2">
            <label class="block text-xs text-gray-600">Unit</label>
            <input type="text"
                   :name="`items[${index}][unit]`"
                   x-model="row.unit"
                   class="w-full rounded-lg border px-2 py-1.5 text-xs bg-gray-100 cursor-not-allowed"
                   readonly>
          </div>

          {{-- Qty --}}
          <div class="col-span-2">
            <label class="block text-xs text-gray-600">Qty *</label>
            <input type="number"
                   step="0.001" min="0.001"
                   :name="`items[${index}][qty]`"
                   x-model="row.qty"
                   class="w-full rounded-lg border px-2 py-1.5 text-xs"
                   required>
          </div>

          {{-- Category (locked) --}}
          <div class="col-span-3 mt-2">
            <label class="block text-xs text-gray-600">Category</label>
            <input type="hidden"
                   :name="`items[${index}][item_category_id]`"
                   x-model="row.item_category_id">
            <input type="text"
                   x-model="row.item_category_name"
                   class="w-full rounded-lg border px-2 py-1.5 text-xs bg-gray-100 cursor-not-allowed"
                   placeholder="--"
                   readonly>
          </div>
        </div>
      </div>
    </template>
  </div>
</div>


    <div>
      <label class="block text-sm font-medium mb-1">Remarks</label>
      <textarea name="remarks" rows="2"
                class="w-full rounded-lg border px-3 py-2"
                placeholder="Optional">{{ old('remarks') }}</textarea>
    </div>

    <div class="flex justify-end">
      <button type="submit"
              @click="validateEmployee"
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
    // ==== EMPLOYEE SEARCH (same as before) ====
    employeeText: '',
    employeeResults: [],
    activeIndex: -1,
    employeeSearchTimer: null,
    selectedEmployeeId: null,
    showEmployeeError: false,
    showNoResults: false,

    // ==== MULTIPLE ITEMS ====
    items: [],

    blankItem() {
      return {
        store_entry_item_id: '',
        item_name: '',
        item_sn: '',
        unit: '',
        qty: '',
        item_category_id: '',
        item_category_name: '',
        // search state per row
        searchText: '',
        searchResults: [],
        searchTimer: null,
        searchActiveIndex: -1,
        showSearchNoResults: false,
      };
    },

    init(prefill) {
      if (prefill) {
        const row = this.blankItem();
        row.store_entry_item_id = prefill.store_entry_item_id;
        row.item_name           = prefill.item_name || '';
        row.item_sn             = prefill.item_sn || '';
        row.unit                = prefill.unit || '';
        row.item_category_id    = prefill.item_category_id || '';
        row.item_category_name  = prefill.item_category_name || '';
        row.searchText          = prefill.item_name || '';
        this.items = [row];
      } else {
        this.items = [this.blankItem()];
      }
    },

    addRow() {
      this.items.push(this.blankItem());
    },

    removeRow(index) {
      if (this.items.length > 1) {
        this.items.splice(index, 1);
      }
    },

    // ==== ITEM SEARCH (from StoreEntryItem) ====
    onItemInput(index) {
      const row = this.items[index];
      if (!row) return;

      if (row.searchTimer) clearTimeout(row.searchTimer);
      row.searchTimer = setTimeout(() => this.searchItem(index), 200);
    },

    onItemBlur(index) {
      const row = this.items[index];
      if (!row) return;

      setTimeout(() => {
        row.searchResults = [];
        row.showSearchNoResults = false;
      }, 200);
    },

    searchItem(index) {
      const row = this.items[index];
      if (!row) return;

      const term = row.searchText.trim();
      if (!term) {
        row.searchResults = [];
        row.searchActiveIndex = -1;
        row.showSearchNoResults = false;
        return;
      }

      fetch(`{{ route('store.entry-items.search') }}?q=${encodeURIComponent(term)}`, {
        headers: { 'Accept': 'application/json' }
      })
        .then(r => r.ok ? r.json() : [])
        .then(json => {
          row.searchResults = Array.isArray(json) ? json : [];
          row.searchActiveIndex = row.searchResults.length ? 0 : -1;
          row.showSearchNoResults = row.searchResults.length === 0 && term.length > 0;
        })
        .catch(() => {
          row.searchResults = [];
          row.searchActiveIndex = -1;
          row.showSearchNoResults = true;
        });
    },

    moveItemSelection(index, step) {
      const row = this.items[index];
      if (!row || !row.searchResults.length) return;
      const len = row.searchResults.length;
      row.searchActiveIndex = (row.searchActiveIndex + step + len) % len;
    },

    chooseItemSelection(index) {
      const row = this.items[index];
      if (!row) return;
      if (row.searchActiveIndex >= 0 && row.searchResults[row.searchActiveIndex]) {
        this.selectItem(index, row.searchActiveIndex);
      }
    },

    selectItem(index, idx2) {
      const row = this.items[index];
      if (!row) return;
      const it = row.searchResults[idx2];
      if (!it) return;

      row.store_entry_item_id = it.id;
      row.item_name           = it.item_name;
      row.item_sn             = it.item_sn;
      row.unit                = it.unit;
      row.item_category_id    = it.item_category_id;
      row.item_category_name  = it.item_category_name;
      row.searchText          = it.text;

      row.searchResults = [];
      row.searchActiveIndex = -1;
      row.showSearchNoResults = false;
    },

    // ==== EMPLOYEE SEARCH (same as you had) ====
    onEmployeeInput() {
      if (this.selectedEmployeeId) {
        this.clearEmployee();
      }
      this.showEmployeeError = false;
      this.searchEmployee();
    },

    onEmployeeBlur() {
      setTimeout(() => {
        this.employeeResults = [];
        this.showNoResults = false;
        if (!this.selectedEmployeeId && this.employeeText) {
          this.showEmployeeError = true;
        }
      }, 200);
    },

    searchEmployee() {
      const term = this.employeeText.trim();
      if (this.employeeSearchTimer) clearTimeout(this.employeeSearchTimer);

      if (!term) {
        this.employeeResults = [];
        this.showNoResults = false;
        this.activeIndex = -1;
        return;
      }

      this.employeeSearchTimer = setTimeout(() => {
        fetch(`{{ route('employees.search') }}?q=${encodeURIComponent(term)}`, {
          headers: { 'Accept': 'application/json' }
        })
        .then(r => r.ok ? r.json() : [])
        .then(json => {
          this.employeeResults = Array.isArray(json) ? json : [];
          this.activeIndex = this.employeeResults.length ? 0 : -1;
          this.showNoResults = this.employeeResults.length === 0 && term.length > 0;
        })
        .catch(() => {
          this.employeeResults = [];
          this.activeIndex = -1;
          this.showNoResults = true;
        });
      }, 200);
    },

    moveSelection(step) {
      if (!this.employeeResults.length) return;
      this.activeIndex = (this.activeIndex + step + this.employeeResults.length) % this.employeeResults.length;
    },

    chooseSelection() {
      if (this.activeIndex >= 0 && this.employeeResults[this.activeIndex]) {
        this.selectEmployee(this.activeIndex);
      }
    },

    selectEmployee(idx) {
      const e = this.employeeResults[idx];
      if (!e) return;
      this.$refs.employeeId.value = e.id;
      this.selectedEmployeeId = e.id;
      this.employeeText = e.text;
      this.employeeResults = [];
      this.activeIndex = -1;
      this.showEmployeeError = false;
      this.showNoResults = false;
    },

    clearEmployee() {
      this.$refs.employeeId.value = '';
      this.selectedEmployeeId = null;
      this.employeeText = '';
      this.employeeResults = [];
      this.activeIndex = -1;
      this.$refs.employeeSearch.focus();
    },

    validateEmployee(e) {
      if (!this.selectedEmployeeId) {
        e.preventDefault();
        this.showEmployeeError = true;
        this.$refs.employeeSearch.focus();
      }
    }
  }
}
</script>
@endpush

