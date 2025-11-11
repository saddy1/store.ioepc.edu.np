@extends('Backend.layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
  <div class="mb-6 flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold">Store Entry — Choose Categories & Brand</h1>
      <p class="text-sm text-gray-500">
        Purchase: <strong>{{ $purchase->purchase_sn }}</strong> •
        Date: <strong>{{ $purchase->purchase_date }}</strong> •
        Supplier: <strong>{{ $purchase->supplier->name ?? '—' }}</strong>
      </p>
    </div>
    <a href="{{ route('purchases.index') }}" class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">← Purchases</a>
  </div>

  <form method="POST" action="{{ route('store.post-from-purchase', $purchase) }}">
    @csrf

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
      <table class="w-full text-left">
        <thead class="bg-gray-50 text-xs uppercase text-gray-600">
          <tr>
            <th class="px-4 py-3 w-12">S.N</th>
            <th class="px-4 py-3">Item</th>
            <th class="px-4 py-3">Item Category</th>
            <th class="px-4 py-3">Product Category</th>
            <th class="px-4 py-3">Brand</th>
            <th class="px-4 py-3 text-right">Qty</th>
            <th class="px-4 py-3 text-right">Rate</th>
            <th class="px-4 py-3 text-right">Total</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @php $sn=1; @endphp
          @foreach($rows as $i => $r)
            <tr class="hover:bg-gray-50">
              <td class="px-4 py-3">{{ $sn++ }}</td>
              <td class="px-4 py-3">
                <div class="font-medium">{{ $r['display_name'] }}</div>
                @if($r['sn'])
                  <div class="text-xs text-gray-500">SN: {{ $r['sn'] }}</div>
                @endif
                @if($r['unit'])
                  <div class="text-xs text-gray-400">Unit: {{ $r['unit'] }}</div>
                @endif

                {{-- Hidden purchase_item_id --}}
                <input type="hidden" name="mapping[{{ $i }}][purchase_item_id]" value="{{ $r['purchase_item_id'] }}">
              </td>

              {{-- Item Category --}}
              <td class="px-4 py-3">
                <select name="mapping[{{ $i }}][item_category_id]"
                        class="w-full rounded-lg border px-3 py-2">
                  <option value="">— Select —</option>
                  @foreach($itemCategories as $c)
                    <option value="{{ $c->id }}" @selected($r['item_category_id']==$c->id)>{{ $c->name_en }}</option>
                  @endforeach
                </select>
              </td>

              {{-- Product Category --}}
              <td class="px-4 py-3">
                <select name="mapping[{{ $i }}][category_id]"
                        class="w-full rounded-lg border px-3 py-2">
                  <option value="">— Select —</option>
                  @foreach($productCategories as $c)
                    <option value="{{ $c->id }}" @selected($r['category_id']==$c->id)>{{ $c->name }}</option>
                  @endforeach
                </select>
              </td>

              {{-- Brand --}}
              <td class="px-4 py-3">
                <select name="mapping[{{ $i }}][brand_id]"
                        class="w-full rounded-lg border px-3 py-2">
                  <option value="">— Select —</option>
                  @foreach($brands as $b)
                    <option value="{{ $b->id }}" @selected($r['brand_id']==$b->id)>{{ $b->name }}</option>
                  @endforeach
                </select>
              </td>

              <td class="px-4 py-3 text-right">{{ number_format($r['qty'], 3) }}</td>
              <td class="px-4 py-3 text-right">{{ number_format($r['rate'], 2) }}</td>
              <td class="px-4 py-3 text-right">{{ number_format($r['total'], 2) }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>

      <div class="px-4 py-3 border-t bg-gray-50 flex justify-end gap-2">
        <a href="{{ route('purchases.show', $purchase) }}"
           class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
          Cancel
        </a>
        <button class="rounded-xl bg-emerald-600 text-white px-5 py-2.5 text-sm font-semibold hover:bg-emerald-700">
          Create Store Entry
        </button>
      </div>
    </div>
  </form>
</div>
@endsection
