@extends('Backend.layouts.app')
@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
  <div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold"> खरिद माग फाराम</h1>
    <a href="{{ route('slips.create') }}" class="rounded-xl bg-gray-900 text-white px-4 py-2.5 text-sm">+ New Slip</a>
  </div>
  <form class="mb-4" method="GET">
    <input class="rounded-lg border px-3 py-2" name="search" value="{{ request('search') }}" placeholder="Search SN/Date/Dept">
    <button class="rounded-lg bg-blue-600 text-white px-3 py-2 text-sm">Search</button>
  </form>
  <div class="rounded-2xl border bg-white shadow-sm overflow-hidden">
    <table class="w-full text-left">
      <thead class="bg-gray-50 text-xs uppercase text-gray-600">
        <tr><th class="px-4 py-3">क्रम संख्या</th><th class="px-4 py-3">माग फारम नं / मिति </th><th class="px-4 py-3">Department</th><th class="px-4 py-3 text-right">Actions</th></tr>
      </thead>
      <tbody class="divide-y">
        @php $sn = ($slips->currentPage()-1)*$slips->perPage()+1; @endphp
        @forelse($slips as $slip)
          <tr class="hover:bg-gray-50">
            <td class="px-4 py-3">{{ $sn++ }}</td>
            <td class="px-4 py-3"><div class="font-medium">{{ $slip->po_sn }}</div><div class="text-xs text-gray-500">{{ $slip->po_date->format('Y-m-d') }}</div></td>
            <td class="px-4 py-3">{{ $slip->department->name ?? '—' }}</td>
            <td class="px-4 py-3 text-right space-x-2">
              <td class="px-4 py-3 text-right space-x-2">
  @if(($slip->purchases_count ?? 0) > 0)
  <a class="text-indigo-600 hover:underline text-sm" href="{{ route('slips.print', $slip) }}">
      खरिद माग फाराम
    </a>
    {{-- Already purchased: hide Edit/Delete/Purchase; show Purchased Slip + Print --}}
    <a class="text-emerald-700 hover:underline text-sm " href="{{ route('purchases.show', parameters: $slip->purchase_slip->purchase_sn) }}">
     स्टोर प्राप्ति
    </a>
    
  @else
    {{-- Not purchased yet: allow Purchase/Edit/Delete + normal Print --}}
    <a class="text-indigo-600 hover:underline text-sm" href="{{ route('purchases.create', ['slip_id' => $slip->id]) }}">
      Purchase
    </a>
    <a class="text-blue-600 hover:underline text-sm" href="{{ route('slips.edit', parameters: $slip->po_sn) }}">Edit</a>
    <form class="inline" method="POST" action="{{ route('slips.destroy', $slip->po_sn) }}" onsubmit="return confirm('Delete slip?')">
      @csrf @method('DELETE')
      <button class="text-red-600 hover:underline text-sm">Delete</button>
    </form>
    <a class="text-emerald-700 hover:underline text-sm" href="{{ route('slips.print', $slip->po_sn) }}">
      Print / PDF
    </a>
  @endif
</td>

            </td>
          </tr>
        @empty
          <tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">No slips found.</td></tr>
        @endforelse
      </tbody>
    </table>
    <div class="px-4 py-3 border-t bg-gray-50">{{ $slips->links() }}</div>
  </div>
</div>
@endsection
