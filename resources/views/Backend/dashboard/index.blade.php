@extends('Backend.layouts.app')

@section('content')
<div class="px-4 py-4">
  {{-- Header + Filters --}}
  <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-5">
    <div>
      <h1 class="text-xl font-semibold text-gray-900">Store Dashboard</h1>
      <p class="text-sm text-gray-500">Stocks • Categories • Suppliers • Expense (Consumable vs Non-Consumable)</p>
    </div>

    <form class="flex flex-wrap items-center gap-2" method="GET">
      <input type="date" name="from" value="{{ $from ?? '' }}"
             class="h-9 rounded-md border border-gray-300 bg-white px-3 text-sm text-gray-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
      <input type="date" name="to" value="{{ $to ?? '' }}"
             class="h-9 rounded-md border border-gray-300 bg-white px-3 text-sm text-gray-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">

      <button type="submit"
              class="h-9 rounded-md bg-blue-600 px-4 text-sm font-medium text-white shadow-sm hover:bg-blue-700">
        Filter
      </button>

      {{-- Change this route if your store dashboard route name differs --}}
      <a href="{{ route('admin.dashboard') }}"
         class="h-9 rounded-md border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
        Reset
      </a>
    </form>
  </div>

  {{-- KPI Cards --}}
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
      <p class="text-sm text-gray-500">Products</p>
      <p class="mt-1 text-2xl font-semibold text-gray-900">{{ $counts['products'] }}</p>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
      <p class="text-sm text-gray-500">Suppliers</p>
      <p class="mt-1 text-2xl font-semibold text-gray-900">{{ $counts['suppliers'] }}</p>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
      <p class="text-sm text-gray-500">Item Categories</p>
      <p class="mt-1 text-2xl font-semibold text-gray-900">{{ $counts['itemCategories'] }}</p>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
      <p class="text-sm text-gray-500">Product Categories</p>
      <p class="mt-1 text-2xl font-semibold text-gray-900">{{ $counts['categories'] }}</p>
    </div>
  </div>

  {{-- Totals + Charts --}}
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-5">
    {{-- Totals Card --}}
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
      <p class="text-sm text-gray-500">Total Purchase Amount (IN)</p>
      <p class="mt-1 text-2xl font-bold text-gray-900">
        Rs. {{ number_format((float)$purchaseTotals->total_amount, 2) }}
      </p>

      <div class="my-4 h-px bg-gray-200"></div>

      <div class="space-y-2">
        <div class="flex items-center justify-between">
          <span class="text-sm text-gray-500">Consumable</span>
          <span class="text-sm font-semibold text-gray-900">
            Rs. {{ number_format((float)$purchaseTotals->consumable_amount, 2) }}
          </span>
        </div>
        <div class="flex items-center justify-between">
          <span class="text-sm text-gray-500">Non-Consumable</span>
          <span class="text-sm font-semibold text-gray-900">
            Rs. {{ number_format((float)$purchaseTotals->non_consumable_amount, 2) }}
          </span>
        </div>
      </div>

      <p class="mt-3 text-sm text-gray-500">
        Total Qty Purchased:
        <span class="font-medium text-gray-800">{{ number_format((float)$purchaseTotals->total_qty, 3) }}</span>
      </p>
    </div>

    {{-- Expense Pie --}}
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
      <p class="text-sm font-semibold text-gray-900 mb-2">Expense Split (IN)</p>
      <div class="h-[260px]">
        <canvas id="expensePie"></canvas>
      </div>
    </div>

    {{-- Stock Pie --}}
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
      <p class="text-sm font-semibold text-gray-900 mb-2">Current Stock Value (Remaining)</p>
      <div class="h-[260px]">
        <canvas id="stockPie"></canvas>
      </div>

      <p class="mt-3 text-sm text-gray-500">
        Remaining Qty:
        <span class="text-gray-800 font-medium">Consumable {{ number_format($stockByType['consumable_qty'],3) }}</span>,
        <span class="text-gray-800 font-medium">Non-Consumable {{ number_format($stockByType['non_qty'],3) }}</span>
      </p>
    </div>
  </div>

  {{-- Charts + Tables --}}
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    {{-- Top Suppliers --}}
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
      <div class="flex items-center justify-between mb-2">
        <p class="text-sm font-semibold text-gray-900">Top Suppliers (Spend)</p>
      </div>
      <div class="h-[220px]">
        <canvas id="supplierBar"></canvas>
      </div>
    </div>

    {{-- Top Categories --}}
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
      <p class="text-sm font-semibold text-gray-900 mb-2">Top Categories (Spend)</p>
      <div class="h-[220px]">
        <canvas id="categoryBar"></canvas>
      </div>
    </div>

    {{-- Low Stock --}}
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
      <p class="text-sm font-semibold text-gray-900 mb-3">Low Stock (Remaining)</p>

      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="border-b border-gray-200 bg-gray-50">
              <th class="px-3 py-2 text-left font-medium text-gray-600">Product</th>
              <th class="px-3 py-2 text-right font-medium text-gray-600">Remaining Qty</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            @forelse($lowStock as $row)
              <tr class="hover:bg-gray-50">
                <td class="px-3 py-2 text-gray-800">{{ $row->product_name }}</td>
                <td class="px-3 py-2 text-right font-medium text-gray-800">{{ number_format((float)$row->remaining_qty, 3) }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="2" class="px-3 py-4 text-center text-gray-500">No data</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <p class="mt-3 text-xs text-gray-500">
        Tip: later you can add “reorder level” per product, but dashboard works even without it.
      </p>
    </div>

    {{-- Recent Store Outs --}}
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
      <p class="text-sm font-semibold text-gray-900 mb-3">Recent Store Outs</p>

      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="border-b border-gray-200 bg-gray-50">
              <th class="px-3 py-2 text-left font-medium text-gray-600">SN</th>
              <th class="px-3 py-2 text-left font-medium text-gray-600">Date (BS)</th>
              <th class="px-3 py-2 text-left font-medium text-gray-600">Department</th>
              <th class="px-3 py-2 text-left font-medium text-gray-600">Employee</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            @forelse($recentOuts as $o)
              <tr class="hover:bg-gray-50">
                <td class="px-3 py-2 text-gray-800">{{ $o->store_out_sn }}</td>
                <td class="px-3 py-2 text-gray-800">{{ $o->store_out_date_bs }}</td>
                <td class="px-3 py-2 text-gray-800">{{ $o->department?->name ?? '-' }}</td>
                <td class="px-3 py-2 text-gray-800">{{ $o->employee?->name ?? '-' }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="px-3 py-4 text-center text-gray-500">No recent store outs</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const expenseData = {
    labels: ['Consumable', 'Non-Consumable'],
    datasets: [{
      data: [
        {{ (float)$purchaseTotals->consumable_amount }},
        {{ (float)$purchaseTotals->non_consumable_amount }}
      ]
    }]
  };

  new Chart(document.getElementById('expensePie'), {
    type: 'doughnut',
    data: expenseData,
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
  });

  const stockData = {
    labels: ['Consumable', 'Non-Consumable'],
    datasets: [{
      data: [
        {{ (float)$stockByType['consumable_value'] }},
        {{ (float)$stockByType['non_value'] }}
      ]
    }]
  };

  new Chart(document.getElementById('stockPie'), {
    type: 'pie',
    data: stockData,
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
  });

  const supplierLabels = {!! json_encode($topSuppliers->pluck('supplier')) !!};
  const supplierAmounts = {!! json_encode($topSuppliers->pluck('amount')) !!};

  new Chart(document.getElementById('supplierBar'), {
    type: 'bar',
    data: { labels: supplierLabels, datasets: [{ label: 'Amount', data: supplierAmounts }] },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
  });

  const catLabels = {!! json_encode($topCategories->pluck('category')) !!};
  const catAmounts = {!! json_encode($topCategories->pluck('amount')) !!};

  new Chart(document.getElementById('categoryBar'), {
    type: 'bar',
    data: { labels: catLabels, datasets: [{ label: 'Amount', data: catAmounts }] },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
  });
</script>
@endsection
