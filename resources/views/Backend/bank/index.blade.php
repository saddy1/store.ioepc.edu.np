@extends('Backend.layouts.app')

@section('title','Upload Bank Details')

@section('content')
<div class="px-4 py-6 max-w-7xl mx-auto">

  <!-- Header + Add -->
  <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3 mb-6">
    <h3 class="text-2xl font-bold text-slate-900">Upload Bank Details</h3>
    <button id="addStudentBtn"
            class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 shadow">
      Add Bank Data
    </button>
  </div>

  <!-- Alerts -->
  @if(session('error'))
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 text-red-800 px-4 py-3">{{ session('error') }}</div>
  @endif
  @if(session('success'))
    <div class="mb-4 rounded-lg border border-green-200 bg-green-50 text-green-800 px-4 py-3">{{ session('success') }}</div>
  @endif
  @if($errors->any())
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 text-red-800 px-4 py-3">
      <ul class="list-disc list-inside text-sm">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <!-- Upload Form (toggle) -->
  <div id="uploadFormContainer" class="hidden mb-6">
    <form action="{{ route('bank.import') }}" method="POST" enctype="multipart/form-data"
          class="bg-white p-6 rounded-xl ring-1 ring-slate-200 shadow-sm">
      @csrf
      <div class="grid gap-4 md:grid-cols-[1fr_auto]">
        <div>
          <label for="file" class="block text-sm font-medium text-slate-700 mb-1">Choose XLS/XLSX File</label>
          <input type="file" name="file" id="file" accept=".xls,.xlsx" required
                 class="w-full rounded-lg border border-slate-300 focus:border-indigo-600 focus:ring-indigo-600 px-3 py-2" />
        </div>
        <div class="flex items-end">
          <button type="submit"
                  class="inline-flex items-center justify-center px-5 py-2.5 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 shadow">
            Import
          </button>
        </div>
      </div>
    </form>
  </div>

  <!-- Filters -->
  <div class="bg-white rounded-xl ring-1 ring-slate-200 shadow-sm p-4 mb-4">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
      <!-- Left: Used / Unused toggles -->
      <div class="flex flex-wrap gap-2">
        @php
          $baseQuery = request()->except('page');
        @endphp
        <a href="{{ route('bank.index', array_merge($baseQuery, ['status' => 'used'])) }}"
           class="px-3 py-1.5 rounded-lg border
                  {{ ($status === 'used') ? 'bg-green-600 text-white border-green-600' : 'bg-white text-slate-700 border-slate-300 hover:bg-slate-50' }}">
          Used Bank Data
        </a>
        <a href="{{ route('bank.index', array_merge($baseQuery, ['status' => 'unused'])) }}"
           class="px-3 py-1.5 rounded-lg border
                  {{ ($status === 'unused') ? 'bg-amber-600 text-white border-amber-600' : 'bg-white text-slate-700 border-slate-300 hover:bg-slate-50' }}">
          Unused Bank Data
        </a>
        <a href="{{ route('bank.index', array_merge($baseQuery, ['status' => null])) }}"
           class="px-3 py-1.5 rounded-lg border bg-white text-slate-700 border-slate-300 hover:bg-slate-50">
          All
        </a>
      </div>

      <!-- Right: search + date + per_page -->
      <form method="GET" class="flex flex-col sm:flex-row gap-3 sm:items-center">
        {{-- Preserve status when searching --}}
        <input type="hidden" name="status" value="{{ $status }}">

        <input type="text" name="q" value="{{ $q }}" placeholder="Search name / token / txn"
               class="w-full sm:w-72 rounded-lg border border-slate-300 focus:border-indigo-600 focus:ring-indigo-600 px-3 py-2" />

        <input type="date" name="date" value="{{ $date }}"
               class="rounded-lg border border-slate-300 focus:border-indigo-600 focus:ring-indigo-600 px-3 py-2" />

        <select name="per_page"
                class="rounded-lg border border-slate-300 focus:border-indigo-600 focus:ring-indigo-600 px-2 py-2"
                onchange="this.form.submit()">
          @foreach(($allowedPerPage ?? [10,20,50,100,200]) as $opt)
            <option value="{{ $opt }}" @selected($perPage == $opt)>{{ $opt }}</option>
          @endforeach
        </select>

        <div class="flex gap-2">
          <button class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-slate-900 text-white hover:bg-black">
            Apply
          </button>
          <a href="{{ route('bank.index') }}"
             class="inline-flex items-center justify-center px-4 py-2 rounded-lg border border-slate-300 hover:bg-slate-50">
            Reset
          </a>
        </div>
      </form>
    </div>
  </div>

  <!-- Table -->
  <div class="bg-white rounded-xl ring-1 ring-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-slate-50 text-slate-700">
          <tr>
            <th class="px-4 py-3 text-left">SN</th>
            <th class="px-4 py-3 text-left">Date</th>
            <th class="px-4 py-3 text-left">Txn ID</th>
            <th class="px-4 py-3 text-left">Name</th>
            <th class="px-4 py-3 text-left">Token No</th>
            <th class="px-4 py-3 text-left">Amount (Rs.)</th>
            <th class="px-4 py-3 text-left">Status</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
          @forelse($transactions as $idx => $t)
            @php $sn = ($transactions->firstItem() ?? 1) + $idx; @endphp
            <tr class="hover:bg-slate-50 {{ $t->status == 2 ? 'bg-green-50' : '' }}">
              <td class="px-4 py-3">{{ $sn }}</td>
              <td class="px-4 py-3">{{ \Illuminate\Support\Carbon::parse($t->date)->format('Y-m-d') }}</td>
              <td class="px-4 py-3 font-mono">{{ $t->txn_id }}</td>
              <td class="px-4 py-3">{{ $t->name }}</td>
              <td class="px-4 py-3 font-mono">{{ $t->token_num ?? '—' }}</td>
              <td class="px-4 py-3">{{ number_format((int) $t->amount) }}</td>
              <td class="px-4 py-3">
                <span class="px-2 py-1 text-xs rounded-full
                  {{ $t->status == 2 ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                  {{ $t->status == 2 ? 'Used' : 'Unused' }}
                </span>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="px-4 py-8 text-center text-slate-500">No transactions found.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <!-- Pagination footer -->
    <div class="px-4 py-3 border-t border-slate-200 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
      <div class="text-sm text-slate-600">
        Showing <span class="font-semibold">{{ $transactions->firstItem() }}</span> to
        <span class="font-semibold">{{ $transactions->lastItem() }}</span> of
        <span class="font-semibold">{{ $transactions->total() }}</span> results
      </div>
      <div>
        {{ $transactions->onEachSide(1)->withQueryString()->links() }}
      </div>
    </div>
  </div>
</div>

<script>
  document.getElementById('addStudentBtn')?.addEventListener('click', () => {
    document.getElementById('uploadFormContainer')?.classList.toggle('hidden');
  });
</script>
@endsection
