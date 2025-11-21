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

      {{-- Employee autocomplete --}}
      <div class="sm:col-span-1">
        <label class="block text-sm font-medium mb-1">Employee *</label>
        <input type="hidden" name="employee_id" x-ref="employeeId">
        <input type="text"
               x-ref="employeeSearch"
               x-model="employeeText"
               @input="searchEmployee"
               @keydown.arrow-down.prevent="moveSelection(1)"
               @keydown.arrow-up.prevent="moveSelection(-1)"
               @keydown.enter.prevent="chooseSelection"
               class="w-full rounded-lg border px-3 py-2"
               placeholder="Type name / email / atten no">
        <div x-show="employeeResults.length"
             class="mt-1 border rounded-lg bg-white shadow max-h-40 overflow-auto text-sm">
          <template x-for="(e,idx) in employeeResults" :key="e.id">
            <div class="px-3 py-2 cursor-pointer"
                 :class="idx === activeIndex ? 'bg-gray-100' : ''"
                 @mousedown.prevent="selectEmployee(idx)">
              <div x-text="e.text"></div>
            </div>
          </template>
        </div>
      </div>
    </div>

    <div>
      <label class="block text-sm font-semibold mb-2">Items to issue *</label>

      <div class="space-y-2">
        {{-- For now single item row; you can expand later --}}
        <div class="grid grid-cols-12 gap-2 items-end">
          <div class="col-span-4">
            <label class="block text-xs text-gray-600">Item Name *</label>
            <input type="text" name="items[0][item_name]"
                   x-model="item.item_name"
                   class="w-full rounded-lg border px-3 py-2" required>
          </div>
          <div class="col-span-2">
            <label class="block text-xs text-gray-600">Detail (SN)</label>
            <input type="text" name="items[0][item_sn]"
                   x-model="item.item_sn"
                   class="w-full rounded-lg border px-3 py-2">
          </div>
          <div class="col-span-2">
            <label class="block text-xs text-gray-600">Unit</label>
            <input type="text" name="items[0][unit]"
                   x-model="item.unit"
                   class="w-full rounded-lg border px-3 py-2">
          </div>
          <div class="col-span-2">
            <label class="block text-xs text-gray-600">Qty *</label>
            <input type="number" step="0.001" min="0.001"
                   name="items[0][qty]"
                   x-model="item.qty"
                   class="w-full rounded-lg border px-3 py-2" required>
          </div>
          <div class="col-span-2">
            <label class="block text-xs text-gray-600">Category</label>
            <select name="items[0][item_category_id]"
                    x-model="item.item_category_id"
                    class="w-full rounded-lg border px-3 py-2">
              <option value="">-- Select --</option>
              @foreach(\App\Models\ItemCategory::orderBy('name_en')->get() as $c)
                <option value="{{ $c->id }}">{{ $c->name_en }} ({{ $c->name_np }})</option>
              @endforeach
            </select>
          </div>
        </div>

        {{-- Hidden field for store_entry_item if we came from category item page --}}
        <template x-if="item.store_entry_item_id">
          <input type="hidden" name="items[0][store_entry_item_id]" :value="item.store_entry_item_id">
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
      <button class="rounded-xl bg-gray-900 text-white px-5 py-2.5 text-sm font-semibold hover:bg-gray-800">
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
    item: {
      store_entry_item_id: null,
      item_name: '',
      item_sn: '',
      unit: '',
      qty: '',
      item_category_id: '',
    },

    init(prefill) {
      if (prefill) {
        this.item.store_entry_item_id = prefill.store_entry_item_id;
        this.item.item_name           = prefill.item_name || '';
        this.item.item_sn             = prefill.item_sn || '';
        this.item.unit                = prefill.unit || '';
        this.item.item_category_id    = prefill.item_category_id || '';
      }
    },

    searchEmployee() {
      const term = this.employeeText.trim();
      if (this.employeeSearchTimer) clearTimeout(this.employeeSearchTimer);

      if (!term) {
        this.employeeResults = [];
        this.activeIndex = -1;
        this.$refs.employeeId.value = '';
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
        })
        .catch(() => {
          this.employeeResults = [];
          this.activeIndex = -1;
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
      this.employeeText = e.text;
      this.employeeResults = [];
      this.activeIndex = -1;
    }
  }
}
</script>
@endpush
