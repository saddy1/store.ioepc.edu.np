@extends('Frontend.layouts.app')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-8">
  <h1 class="text-2xl font-bold mb-4">Items Issued to You</h1>

  <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
    <table class="w-full text-sm">
      <thead class="bg-gray-50 text-xs uppercase text-gray-600">
        <tr>
          <th class="px-4 py-3">Date (BS)</th>
          <th class="px-4 py-3">Store Out SN</th>
          <th class="px-4 py-3">Item</th>
          <th class="px-4 py-3">Unit</th>
          <th class="px-4 py-3">Qty</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        @forelse($outs as $o)
          @foreach($o->items as $it)
            <tr class="hover:bg-gray-50">
              <td class="px-4 py-3">{{ $o->store_out_date_bs }}</td>
              <td class="px-4 py-3">{{ $o->store_out_sn }}</td>
              <td class="px-4 py-3">
                <div class="font-medium text-gray-900">{{ $it->item_name }}</div>
                <div class="text-xs text-gray-500">{{ $it->item_sn }}</div>
              </td>
              <td class="px-4 py-3">{{ $it->unit ?: '—' }}</td>
              <td class="px-4 py-3">{{ number_format($it->qty,3) }}</td>
            </tr>
          @endforeach
        @empty
          <tr>
            <td colspan="5" class="px-4 py-6 text-center text-gray-500">
              No items issued yet.
            </td>
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
