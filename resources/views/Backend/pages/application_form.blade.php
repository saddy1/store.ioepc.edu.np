@extends('Backend.layouts.app')

@section('title', 'Applications')

@section('content')
<div class="max-w-7xl mt-5 mx-auto px-4 sm:px-6 lg:px-8">

    <!-- Heading & Filters -->
    <div class="mb-6  flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Application Forms</h1>
            <p class="text-sm text-slate-600">Review vouchers and confirm payments.</p>
        </div>

        <form method="GET" class="flex flex-col sm:flex-row gap-3 sm:items-center">
            <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Search by token or roll number"
                class="w-full sm:w-72 p-2 border border-green-300 focus:border-indigo-600 focus:ring-indigo-600" />
            
            <div class="flex items-center gap-2">
                <label for="per_page" class="text-sm text-slate-600">Rows</label>
                <select id="per_page" name="per_page"
                    class="rounded-lg border-gray-300 focus:border-indigo-600 focus:ring-indigo-600"
                    onchange="this.form.submit()">
                    @foreach ($allowedPerPage ?? [10, 20, 50, 100, 200] as $opt)
                        <option value="{{ $opt }}" @selected(($perPage ?? 10) == $opt)>{{ $opt }}</option>
                    @endforeach
                </select>
            </div>

            <button class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-slate-900 text-white hover:bg-black">
                Apply
            </button>
        </form>
    </div>

    <!-- Alerts -->
    @if (session('success'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 text-green-800 px-4 py-3">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 text-red-800 px-4 py-3">
            {{ session('error') }}
        </div>
    @endif

    <!-- Table -->
    <div class="bg-white rounded-2xl ring-1 ring-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-700">
                    <tr>
                        <th class="px-4 py-3 text-left">SN</th>
                        <th class="px-4 py-3 text-left">Student</th>
                        <th class="px-4 py-3 text-left">Token</th>
                        <th class="px-4 py-3 text-left">Roll</th>
                        <th class="px-4 py-3 text-left">Amount (Rs.)</th>
                        <th class="px-4 py-3 text-left">Payment ID</th>
                        <th class="px-4 py-3 text-left">Voucher</th>
                        <th class="px-4 py-3 text-left">Confirmation</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($applications as $idx => $app)
                        @php
                            $s = $app->student;
                            $sn = ($applications->firstItem() ?? 1) + $idx;
                        @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3">{{ $sn }}</td>
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $s->name ?? '—' }}</td>
                            <td class="px-4 py-3 font-mono">{{ $app->token_num }}</td>
                            <td class="px-4 py-3 font-mono">{{ $s->roll_num ?? '—' }}</td>
                            <td class="px-4 py-3">{{ number_format((int) ($s->amount ?? 0)) }}</td>
                            <td class="px-4 py-3 font-mono">{{ $s->payment_id ?? '—' }}</td>

                            <!-- Voucher -->
                            <td class="px-4 py-3">
                                @if ($app->payment_image)
                                    <div class="flex items-center gap-2">
                                        <a href="{{ asset('storage/' . $app->payment_image) }}" target="_blank"
                                            class="inline-flex items-center justify-center w-9 h-9 rounded-md border border-slate-300 hover:bg-slate-100">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5 fill-slate-700">
                                                <path d="M12 5c-7 0-10 7-10 7s3 7 10 7 10-7 10-7-3-7-10-7Zm0 12a5 5 0 1 1 0-10 5 5 0 0 1 0 10Zm0-2.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z"/>
                                            </svg>
                                        </a>
                                        <button type="button" data-pdf="{{ asset('storage/' . $app->payment_image) }}"
                                            class="print-btn inline-flex items-center justify-center w-9 h-9 rounded-md border border-slate-300 hover:bg-slate-100">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5 fill-slate-700">
                                                <path d="M17 3H7v4h10V3Zm1 6H6a3 3 0 0 0-3 3v3h4v4h10v-4h4v-3a3 3 0 0 0-3-3Zm-3 9H9v-4h6v4Z"/>
                                            </svg>
                                        </button>
                                    </div>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>

                            <!-- Confirmation -->
                            <td class="px-4 py-3">
                                @if ($app->voucher_image)
                                    <div class="flex items-center gap-2">
                                        <a href="{{ asset('storage/' . $app->voucher_image) }}" target="_blank"
                                            class="inline-flex items-center justify-center w-9 h-9 rounded-md border border-slate-300 hover:bg-slate-100">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5 fill-slate-700">
                                                <path d="M12 5c-7 0-10 7-10 7s3 7 10 7 10-7 10-7-3-7-10-7Zm0 12a5 5 0 1 1 0-10 5 5 0 0 1 0 10Zm0-2.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z"/>
                                            </svg>
                                        </a>
                                        <button type="button" data-pdf="{{ asset('storage/' . $app->voucher_image) }}"
                                            class="print-btn inline-flex items-center justify-center w-9 h-9 rounded-md border border-slate-300 hover:bg-slate-100">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5 fill-slate-700">
                                                <path d="M17 3H7v4h10V3Zm1 6H6a3 3 0 0 0-3 3v3h4v4h10v-4h4v-3a3 3 0 0 0-3-3Zm-3 9H9v-4h6v4Z"/>
                                            </svg>
                                        </button>
                                    </div>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="px-4 py-3">
                                @php $status = strtolower($app->status ?? 'pending'); @endphp
                                <span class="px-2 py-1 text-xs rounded-full
                                    @if ($status === 'active' || $status === 'approved') bg-green-100 text-green-700
                                    @elseif($status === 'pending') bg-amber-100 text-amber-700
                                    @else bg-slate-100 text-slate-700 @endif">
                                    {{ ucfirst($status) }}
                                </span>
                            </td>

                            <!-- Action -->
                            <td class="px-4 py-3">
                                @if (($app->status ?? 'pending') === 'pending')
                                    <form method="POST" action="{{ route('applications.approve', $app->token_num) }}">
                                        @csrf
                                        <button class="px-3 py-1.5 rounded-md bg-emerald-600 text-white hover:bg-emerald-700">
                                            Approve
                                        </button>
                                    </form>
                                @else
                                    <span class="text-slate-500">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-8 text-center text-slate-500">
                                No applications found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-4 py-3 border-t border-slate-200 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div class="text-sm text-slate-600">
                Showing <span class="font-semibold">{{ $applications->firstItem() }}</span>
                to <span class="font-semibold">{{ $applications->lastItem() }}</span>
                of <span class="font-semibold">{{ $applications->total() }}</span> results
            </div>
            <div>
                {{ $applications->onEachSide(1)->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.print-btn');
        if (!btn) return;
        const url = btn.getAttribute('data-pdf');
        if (!url) return;

        const w = window.open('', '_blank', 'width=900,height=700');
        if (!w) return;
        w.document.write(`
            <!doctype html>
            <title>Print PDF</title>
            <style>html,body,iframe{height:100%;margin:0;padding:0;width:100%}</style>
            <iframe src="${url}" frameborder="0"></iframe>
            <script>window.onload = function(){ setTimeout(function(){ window.print(); }, 500); }<\/script>
        `);
        w.document.close();
    });
</script>
@endsection
