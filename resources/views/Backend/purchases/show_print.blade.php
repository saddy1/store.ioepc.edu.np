@extends('Backend.layouts.app')
@section('content')
    <style>
        @page {
            size: A4;
            margin: 14mm 12mm;
        }

        body {
            font-family: "Noto Sans Devanagari", "Mangal", "Kalimati", Arial, sans-serif;
            color: #111;
        }

        .a4 {
            width: 190mm;
            margin: 0 auto;
        }

        .line {
            border-top: 1px solid #000;
            margin: 6px 0 12px;
        }

        @media screen {
            body {
                background: #f9fafb;
                padding: 24px;
            }

            .a4 {
                background: #fff;
                padding: 12mm;
                box-shadow: 0 4px 20px rgba(0, 0, 0, .1);
                border-radius: 8px;
            }

            .printbar {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 8px;
                margin-bottom: 16px;
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

        /* ===== PRINT ONLY THE A4 BLOCK ===== */
        @media print {

            /* Hide common layout chrome */
            .printbar,
            header,
            nav,
            aside,
            .sidebar,
            .navbar,
            .app-header,
            .app-footer,
            .site-header,
            .site-footer {
                display: none !important;
            }

            /* Hide everything by default */
            body * {
                visibility: hidden !important;
            }

            /* Show only the printable block */
            #printable,
            #printable * {
                visibility: visible !important;
            }

            /* Position printable block at page origin & remove on-screen padding/bg */
            body {
                padding: 0 !important;
                background: #fff !important;
            }

            #printable {
                position: absolute;
                left: 0;
                top: 0;
                width: 190mm;
                /* match .a4 width */
                margin: 0 !important;
                box-shadow: none !important;
                border-radius: 0 !important;
            }

            /* Keep footer of the A4 block at bottom of page if you want */
            footer {
                position: fixed;
                bottom: 14mm;
                left: 0;
                right: 0;
            }
        }

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .a4 {
            display: flex;
            flex-direction: column;
            min-height: 100%;
        }

        footer {
            margin-top: auto;
        }
    </style>

    <!-- Screen header with actions (won't print) -->
    <div class="printbar no-print">
        <div class="text-sm text-gray-600">
            <a href="{{ route('purchases.index') }}">← Back</a>
        </div>
        <div class="space-x-2">
            @if ($purchase->purchase_slip_id)
                <a href="{{ route('slips.show', $purchase->purchase_slip_id) }}">View Slip</a>
            @endif
            <button onclick="window.print()">🖨️ Print / Save as PDF</button>
        </div>
    </div>

    {{-- ✅ Add id="printable" to ensure only this prints --}}
    <div id="printable" class="a4">
        <!-- Top-right code (optional) -->
        <div class="text-right text-xs text-gray-700 mb-2">त्रि. वि फा. नं. १९</div>

        <!-- Header -->
        <div class="text-center leading-tight mb-6">
            <div class="text-xl font-bold">त्रिभुवन विश्वविद्यालय</div>
            <div class="text-base font-medium mt-1">पूर्वाञ्चल क्याम्पस, धरान</div>
            <div class="text-lg font-semibold mt-2 ">स्टोर प्राप्ति तथा निरीक्षण नोट </div>
        </div>



        <div>
            <div class="text-right  mb-2">क्रम संख्या : <strong>{{ $meta['purchase_sn'] }}</strong> </div>

            श्री <strong>{{ $meta['supplier'] }}</strong> बाट निम्नलिखित माल प्राप्त भयो ।

        </div>
        <!-- Metadata grid -->
        <br>

        <div class="grid grid-cols-2 sm:grid-cols-2 gap-y-1 text-sm mb-3">
            <div>बिल नं.: <strong>{{ $meta['bill_no'] }}</strong></div>
            <div class="text-right">मिति: <strong>{{ $meta['purchase_date'] ?? '—' }}</strong></div>


        </div>

        {{-- <div class="line"></div> --}}



        <!-- Items table -->
        <div class="overflow-x-auto">
            <table class="w-full border border-black border-collapse text-sm leading-snug">
                <thead>
                    <tr>
                        <th class="border border-black px-2 py-1 text-center w-10">सि.नं</th>
                        <th class="border border-black px-2 py-1 text-left w-56">विवरण (Name)</th>

                        {{-- ✅ New merged Slip No/Date column after description --}}
                        <th class="border border-black px-2 py-1 text-center w-40">Order Slip No / Date</th>

                        <th class="border border-black px-2 py-1 text-center w-10">एकाई</th>
                        <th class="border border-black px-2 py-1 text-center w-10">परिमाण</th>
                        <th class="border border-black px-2 py-1 text-center w-15">दर</th>
                        <th class="border border-black px-2 py-1 text-center w-24">रकम</th>
                        <th class="border border-black px-2 py-1 text-center w-10">Ledger No</th>
                        <th class="border border-black px-2 py-1 text-center w-20">कैफियत</th>
                    </tr>
                </thead>

                <tbody>
                    @php
                        $total = 0;
                        $rowCount = max(count($items), 1); // number of item rows
                    @endphp

                    @forelse($items as $i => $row)
                        @php
                            $amt = (float) str_replace(',', '', $row['amount'] ?? 0);
                            $total += $amt;
                        @endphp
                        <tr class="align-top">
                            <td class="border border-black text-center">{{ $row['sn'] }}</td>
                            <td class="border border-black px-2 break-words">{{ $row['desc'] }}</td>

                            {{-- ✅ Slip info (merged once, right after Name) --}}
                            @if ($i === 0)
                                <td rowspan="{{ $rowCount }}"
                                    class="border border-black px-2 py-2 text-sm text-center align-middle">
                                    <div class="leading-snug">
                                        <div><span class="font-medium"></span> {{ $meta['slip_sn'] ?? '—' }}</div>
                                        <div><span class="font-medium"></span> {{ $meta['slip_date'] ?? '—' }}</div>
                                    </div>
                                </td>
                            @endif

                            <td class="border border-black text-center">{{ $row['unit'] ?: '—' }}</td>
                            <td class="border border-black text-center">{{ $row['required'] }}</td>
                            <td class="border border-black text-center">{{ $row['rate'] }}</td>
                            <td class="border border-black text-center">{{ $row['amount'] }}</td>
                            <td class="border border-black text-center">{{ $row['store_entry_sn']?:'-' }}</td>

                            {{-- ✅ Merged remarks (spans all item rows) --}}
                            @if ($i === 0)
                                <td rowspan="{{ $rowCount }}" class="border border-black p-0 align-middle">
                                    <div
                                        class="h-full w-full flex items-center justify-center text-[13px] font-medium text-gray-800 px-2 text-center">
                                        {{ $meta['remarks'] }}
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-gray-600">
                                कुनै सामग्री खरिद विवरण उपलब्ध छैन ।
                            </td>
                        </tr>
                    @endforelse

                    @if (count($items))
                        {{-- Totals rows (keep same number of columns for alignment) --}}
                        <tr class="font-semibold">
                            <td colspan="6" class="border border-black text-center px-3 py-2">उप-योग (Sub Total)</td>
                            <td  colspan="3" class="border border-black text-center px-3 py-2">{{ $meta['sub_total'] }}</td>
                           
                        </tr>
                        @if ($meta['tax_mode'] === 'VAT')
                            <tr class="font-semibold">
                                <td colspan="6" class="border border-black text-center px-3 py-2">
                                    VAT @if ($meta['tax_mode'] === 'VAT')
                                        ({{ $meta['vat_percent'] }}%)
                                    @endif
                                </td>
                                <td  colspan="3" class="border border-black text-center px-3 py-2">{{ $meta['vat_amount'] }}</td>
                                
                            </tr>
                        @endif
                        <tr class="font-bold">
                            <td colspan="6" class="border border-black text-center px-3 py-2">जम्मा रकम (Grand Total)</td>
                            <td colspan="3"class="border border-black text-center px-3 py-2">{{ $meta['grand_total'] }}</td>
                            
                        </tr>
                    @endif
                </tbody>

            </table>
        </div>

        <!-- Footer / signatures (stays at bottom in print) -->
        <footer class="mt-auto pt-8">
            <div class="flex justify-between text-sm mt-10">
                <div class="text-left w-1/2">
                    <div class="mt-6 leading-tight">
                        स्टोरमा बुझिलिने  ..........................<br>
                        मिति : ..........................
                    </div>
                </div>
                
                <div class="text-right w-1/2">
                    <div class="mt-6 leading-tight">
                        निरीक्षण गरी स्वीकृति दिने  ......................<br>
                         मिति : ..........................
                    </div>
                </div>
            </div>

            
        </footer>
    </div>
@endsection
