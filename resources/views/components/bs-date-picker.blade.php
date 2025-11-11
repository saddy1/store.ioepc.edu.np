@props([
  'name' => 'po_date_bs',          // the hidden field name to submit
  'initialBsYear' => 2082,         // default year to open
  'initialBsMonth' => 1,           // default month to open
  'value' => null,                 // preselected value "YYYY-MM-DD" in BS
  'label' => 'PO Date (BS)',       // field label
])

<div x-data="bsDatePicker({
        name: @js($name),
        initialValue: @js($value),
        initialYear: {{ (int)$initialBsYear }},
        initialMonth: {{ (int)$initialBsMonth }},
        api: '/api/bs-month'
     })" class="w-full">

  <label class="block text-sm font-medium text-gray-700 mb-1">{{ $label }}</label>

  <!-- Visible readonly input to show selected BS date -->
  <div class="relative">
    <input type="text" x-model="display" readonly
           class="w-full rounded-lg border border-gray-300 px-3 py-2 pr-10 focus:border-gray-400 focus:ring-0"
           placeholder="YYYY-MM-DD (BS)">
    <button type="button" @click="open = !open"
            class="absolute inset-y-0 right-2 flex items-center text-gray-500 hover:text-gray-700">
      <!-- calendar icon -->
      <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
        <rect x="3" y="4" width="18" height="18" rx="2" />
        <path d="M16 2v4M8 2v4M3 10h18"/>
      </svg>
    </button>
  </div>

  <!-- Hidden field that actually posts -->
  <input type="hidden" :name="name" x-model="value">

  <!-- Popover Calendar -->
  <div x-show="open" @click.outside="open=false" x-transition
       class="mt-2 w-full sm:w-80 rounded-2xl border border-gray-200 bg-white shadow-lg">

    <!-- Header -->
    <div class="flex items-center justify-between px-4 py-3 border-b bg-gray-50">
      <div class="flex items-center gap-2">
        <button type="button" @click="prevMonth()"
                class="rounded-md border border-gray-200 px-2 py-1 text-sm hover:bg-gray-100">&larr;</button>
        <button type="button" @click="nextMonth()"
                class="rounded-md border border-gray-200 px-2 py-1 text-sm hover:bg-gray-100">&rarr;</button>
      </div>
      <div class="text-sm font-semibold text-gray-800" x-text="`${bsYear}-${String(bsMonth).padStart(2,'0')}`"></div>
      <div></div>
    </div>

    <!-- Weekday header -->
    <div class="grid grid-cols-7 gap-px bg-gray-100 px-3 pt-3 text-[11px] text-gray-500">
      <div class="text-center">Sun</div>
      <div class="text-center">Mon</div>
      <div class="text-center">Tue</div>
      <div class="text-center">Wed</div>
      <div class="text-center">Thu</div>
      <div class="text-center">Fri</div>
      <div class="text-center">Sat</div>
    </div>

    <!-- Days grid -->
    <div class="grid grid-cols-7 gap-1 p-3">
      <template x-for="cell in grid" :key="`${cell.bsY}-${cell.bsM}-${cell.bsD}-${cell.isCurrentMonth}`">
        <button type="button"
                @click="pick(cell)"
                :disabled="!cell.isCurrentMonth"
                class="h-10 rounded-lg border text-sm
                       flex items-center justify-center
                       transition
                       "
                :class="[
                  !cell.isCurrentMonth ? 'bg-gray-50 text-gray-300 border-gray-100 cursor-not-allowed' : 'bg-white hover:bg-gray-50 border-gray-200 text-gray-800',
                  isSelected(cell) ? 'ring-2 ring-blue-500 border-blue-500' : ''
                ]"
                x-text="cell.bsD">
        </button>
      </template>
    </div>
  </div>
</div>

@once
@push('scripts')
<script>
function bsDatePicker({name, initialValue, initialYear, initialMonth, api}) {
  return {
    name,
    open: false,
    api,
    bsYear: initialYear,
    bsMonth: initialMonth,
    grid: [],
    // selected value (YYYY-MM-DD in BS)
    value: initialValue || '',
    get display() { return this.value || ''; },

    async fetchGrid() {
      try {
        const p = new URLSearchParams({ year: this.bsYear, month: this.bsMonth });
        const res = await fetch(`${this.api}?${p.toString()}`, { headers: { 'Accept':'application/json' }});
        if (!res.ok) throw new Error('Failed to load BS month');
        const json = await res.json();
        // expecting { year, month, grid: [ { bsY, bsM, bsD, ad, isCurrentMonth } ... ] }
        this.grid = json.grid || [];
      } catch (e) {
        console.error(e);
        this.grid = [];
      }
    },
    async init() {
      // If initialValue exists, parse and open that month
      if (this.value && /^\d{4}-\d{2}-\d{2}$/.test(this.value)) {
        const [y, m] = this.value.split('-').map(Number);
        this.bsYear = y; this.bsMonth = m;
      }
      await this.fetchGrid();
    },
    async prevMonth() {
      this.bsMonth -= 1;
      if (this.bsMonth < 1) { this.bsMonth = 12; this.bsYear -= 1; }
      await this.fetchGrid();
    },
    async nextMonth() {
      this.bsMonth += 1;
      if (this.bsMonth > 12) { this.bsMonth = 1; this.bsYear += 1; }
      await this.fetchGrid();
    },
    pick(cell) {
      if (!cell.isCurrentMonth) return;
      const y = String(cell.bsY).padStart(4,'0');
      const m = String(cell.bsM).padStart(2,'0');
      const d = String(cell.bsD).padStart(2,'0');
      this.value = `${y}-${m}-${d}`;
      this.open = false;
    },
    isSelected(cell) {
      if (!this.value) return false;
      const [y, m, d] = this.value.split('-').map(Number);
      return cell.bsY === y && cell.bsM === m && cell.bsD === d;
    }
  }
}
</script>
@endpush
@endonce
