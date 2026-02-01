@extends('Backend.layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto px-4 py-8 bg-gray-100">
        
        <div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold">स्टोर प्राप्ति</h1>

    <div class="flex gap-2">
        <a href="{{ route('purchases.create') }}"
           class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
            + स्टोर प्राप्ति 
        </a>

        <a href="{{ route('slips.index') }}"
           class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
            ← खरिद माग फाराम
        </a>
    </div>
</div>


        <form method="GET" action="{{ route('purchases.index') }}" class="mb-6">
            <div class="flex flex-col sm:flex-row gap-3 sm:items-center">
                <input type="text" name="search" value="{{ request('search') }}"
                    class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400"
                    placeholder="Search by Purchase SN / Date / Supplier / Slip SN">
                <button class="rounded-xl bg-blue-600 text-white px-5 py-2.5 text-sm hover:bg-blue-700">Search</button>
            </div>
        </form>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                    <tr>
                        <th class="px-4 py-3 w-16">क्रम संख्या</th>
                        <th class="px-4 py-3">स्टोर प्राप्ति नं / मिति </th>
                        <th class="px-4 py-3">Supplier</th>
                        <th class="px-4 py-3">खरिद माग फाराम</th>
                        <th class="px-4 py-3">Grand Total</th>
                        <th class="px-4 py-3">Store</th> {{-- ✅ new --}}

                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @php $sn = ($orders->currentPage()-1)*$orders->perPage()+1; @endphp
                    @forelse($orders as $o)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">{{ $sn++ }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium">{{ $o->purchase_sn }}</div>
                                <div class="text-xs text-gray-500">{{ optional($o->purchase_date)->format('Y-m-d') }}</div>
                            </td>
                            <td class="px-4 py-3">{{ $o->supplier->name ?? '—' }}</td>
                           <td class="px-4 py-3">
    @php
        $slips = $o->slipNumbers(); // from model accessor
    @endphp

    @if(count($slips))
        <div class="space-y-1">
            @foreach($slips as $sn)
                <a class="text-blue-600 hover:underline text-sm block"
                   href="{{ route('slips.print', $sn) }}">
                    खरिद माग फाराम #{{ $sn }}
                </a>
            @endforeach
        </div>
    @else
        <span class="text-gray-500 text-sm italic">Tender/Quotation (No Slip)</span>
    @endif
</td>

                            <td class="px-4 py-3">{{ number_format($o->grand_total ?: $o->total_amount, 2) }}</td>

                            <td class="px-4 py-3">
                                @if ($o->storeEntry)
                                    <a href="{{ route('store.show', $o->storeEntry) }}"
                                        class="text-emerald-700 hover:underline text-sm">
                                        View Store Entry
                                    </a>
                                @else
                                    <a href="{{ route('store.prepare', $o) }}"
                                        class="text-indigo-600 hover:underline text-sm">
                                        Create Store Entry
                                    </a>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">


                                <a class="text-blue-600 hover:underline text-sm"
                                    href="{{ route('purchases.show', $o->purchase_sn) }}">View/Download</a>

                                @if (!$o->storeEntry)
                                    <span class="mx-1 text-gray-300">|</span>
                                    <a class="text-indigo-600 hover:underline text-sm"
                                        href="{{ route('purchases.edit', $o) }}">Edit</a>
                                @else
                                    <span class="mx-1 text-gray-300">|</span>
                                    <span class="text-gray-400 text-sm italic">Locked (Store done)</span>
                                @endif

                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-gray-500">No purchases found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-4 py-3 border-t bg-gray-50">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
@endsection
