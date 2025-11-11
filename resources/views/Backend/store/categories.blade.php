@extends('Backend.layouts.app')
@section('content')
    <style>
        @media screen {
            .printbar {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 8px;
                margin-bottom: 16px;
                flex-wrap: wrap;
            }

            .printbar a,
            .printbar button {
                padding: 8px 16px;
                border: 1px solid #d1d5db;
                border-radius: 8px;
                background: white;
                font-size: 14px;
            }

            .printbar a:hover,
            .printbar button:hover {
                background: #f3f4f6;
            }
        }
    </style>

    <div class="max-w-6xl mx-auto px-4 py-8">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold">Store: Browse by Product Category</h1>
            <a href="{{ route('store.index') }}"
                class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                ← Store Entries
            </a>
        </div>

        <div class="printbar">
            <form method="GET" class="flex gap-2 items-center flex-wrap">
                <input type="date" name="from" value="{{ $filters['from'] }}" class="rounded border px-3 py-2 text-sm">
                <input type="date" name="to" value="{{ $filters['to'] }}" class="rounded border px-3 py-2 text-sm">
                <button class="rounded bg-blue-600 text-white px-4 py-2 text-sm">Filter</button>
                @if ($filters['from'] || $filters['to'])
                    <a href="{{ route('store.categories') }}" class="text-sm text-gray-700">Reset</a>
                @endif
            </form>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                    <tr>
                        <th class="px-4 py-3 w-16">S.N</th>
                        <th class="px-4 py-3">Product Category</th>
                        <th class="px-4 py-3">Items in Store</th>
                        <th class="px-4 py-3">Total Amount</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($rows as $i => $r)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">{{ $i + 1 }}</td>
                            <td class="px-4 py-3 font-medium">{{ $r['category_name'] }}</td>
                            <td class="px-4 py-3">{{ $r['items_count'] }}</td>
                            <td class="px-4 py-3">{{ $r['total_amount'] }}</td>
                            <td class="px-4 py-3 text-right">
                                <a class="text-indigo-600 hover:underline text-sm"
                                    href="{{ route('store.categories.show', $r['category_id']) }}">
                                    View Ledger
                                </a>
                                <span class="mx-1 text-gray-300">|</span>
                                <a class="text-blue-700 hover:underline text-sm"
                                    href="{{ route('store.categories.items', $r['category_id']) }}">
                                    View Items
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                                No categories found in Store
                                Entries{{ $filters['from'] || $filters['to'] ? ' for the selected period' : '' }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
@endsection
