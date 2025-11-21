@extends('Backend.layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
  <div class="mb-6 flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold">Store OUT Records</h1>
    </div>
    <a href="{{ route('store.out.create') }}"
       class="rounded-xl bg-gray-900 text-white px-4 py-2.5 text-sm font-semibold hover:bg-gray-800">
      + New Store OUT
    </a>
  </div>

  @if (session('success'))
    <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">
      {{ session('success') }}
    </div>
  @endif

  <form method="GET" class="mb-4">
    <input type="text" name="search" value="{{ request('search') }}"
           class="w-full sm:w-72 rounded-lg border px-3 py-2 text-sm"
           placeholder="Search SN / date / employee">
  </form>

  <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
    <table class="w-full text-sm">
      <thead class="bg-gray-50 text-xs uppercase text-gray-600">
        <tr>
          <th class="px-4 py-3 w-16">S.N</th>
          <th class="px-4 py-3">Store Out SN</th>
          <th class="px-4 py-3">Date (BS)</th>
          <th class="px-4 py-3">Employee</th>
          <th class="px-4 py-3">Items</th>
          <th class="px-4 py-3">Remarks</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        @php $sn = ($outs->currentPage()-1)*$outs->perPage() + 1; @endphp
        @forelse($outs as $o)
          <tr class="hover:bg-gray-50">
            <td class="px-4 py-3">{{ $sn++ }}</td>
            <td class="px-4 py-3">
              <a href="{{ route('store.out.show',$o) }}"
                 class="text-blue-600 hover:underline">
                {{ $o->store_out_sn }}
              </a>
            </td>
            <td class="px-4 py-3">{{ $o->store_out_date_bs }}</td>
            <td class="px-4 py-3">{{ $o->employee->full_name ?? '—' }}</td>
            <td class="px-4 py-3">
              {{ $o->items->count() }} item(s)
            </td>
            <td class="px-4 py-3 text-xs text-gray-500">
              {{ \Illuminate\Support\Str::limit($o->remarks, 50) }}
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="px-4 py-6 text-center text-gray-500">No store out records found.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
    <div class="px-4 py-3 border-t bg-gray-50">
      {{ $outs->links() }}
    </div>
  </div>
</div>
@endsection
