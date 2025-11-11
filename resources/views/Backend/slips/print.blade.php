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
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    .a4 {
        width: 190mm;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        min-height: 100%;
    }

    .line {
        border-top: 1px solid #000;
        margin: 6px 0 12px;
    }

    /* ===== SCREEN STYLE ===== */
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
            justify-content: end;
            gap: 8px;
            margin-bottom: 16px;
        }

        button {
            padding: 8px 16px;
            border: 1px solid #ccc;
            border-radius: 8px;
            background: white;
            font-size: 14px;
            cursor: pointer;
        }

        button:hover {
            background: #f3f4f6;
        }
    }

    /* ===== PRINT STYLE ===== */
    @media print {
        .printbar,
        header,
        nav,
        aside,
        .sidebar,
        .navbar,
        .footer,
        .app-header,
        .app-footer {
            display: none !important;
        }

        body * {
            visibility: hidden !important;
        }

        .a4,
        .a4 * {
            visibility: visible !important;
        }

        .a4 {
            position: absolute;
            left: 0;
            top: 0;
            margin: 0 !important;
            box-shadow: none !important;
            border-radius: 0 !important;
            width: 190mm;
        }

        footer {
            position: fixed;
            bottom: 14mm;
            left: 0;
            right: 0;
        }
    }
</style>

<!-- Print button (screen only) -->
<div class="printbar">
    <button onclick="window.print()">🖨️ Print / Save as PDF</button>
</div>

<!-- Printable content -->
<div class="a4" id="printable">
    <!-- Top-right code -->
    <div class="text-right text-xs text-gray-700 mb-2">त्रि. वि फा. नं. १७</div>

    <!-- Header -->
    <div class="text-center leading-tight mb-6">
        <div class="text-xl font-bold">त्रिभुवन विश्वविद्यालय</div>
        <div class="text-base font-medium mt-1">पूर्वाञ्चल क्याम्पस, धरान</div>
        <div class="text-lg font-semibold mt-2 underline underline-offset-4">खरिद माग फाराम</div>
    </div>

    <!-- Metadata -->
    <div class="flex justify-between text-sm mb-3">
        <div>क्र.स.: <strong>{{ $slip->po_sn }}</strong></div>
        <div>मिति: 
            <strong>
                {{ optional(\Illuminate\Support\Carbon::parse($slip->po_date))->format('Y-m-d') ?? '—' }}
            </strong>
        </div>
    </div>

    <div class="line"></div>

    <div class="text-sm mb-2">श्रीमान क्याम्पस प्रमुखज्यू,</div>
    <p class="text-sm mt-4 mb-3 leading-relaxed">
        निम्न विवरण अनुसारको मालसामानको आवश्यकता परेको हुँदा खरिदका लागि आवश्यक व्यवस्था गरिदिनुहुन अनुरोध गर्दछु।
    </p>
    <div class="text-sm mb-3 text-right">
        <span class="font-medium">
            ...............................<br>स्टोर किपर, माग गर्ने
        </span>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full border border-black border-collapse text-sm leading-snug">
            <thead>
                <tr>
                    <th class="border border-black px-2 py-1 text-center w-10">सि.नं</th>
                    <th class="border border-black px-2 py-1 text-left w-56">मालसामानको विवरण</th>
                    <th class="border border-black px-2 py-1 text-center w-14">एकाई</th>
                    <th class="border border-black px-2 py-1 text-right w-20">न्यूनतम मौज्दात</th>
                    <th class="border border-black px-2 py-1 text-right w-20">स्टोर मौज्दात</th>
                    <th class="border border-black px-2 py-1 text-right w-20">आवश्यक परिमाण</th>
                    <th class="border border-black px-2 py-1 text-right w-24">रकम</th>
                    <th class="border border-black px-2 py-1 text-center w-20">बजेट कोड नं.</th>
                    <th class="border border-black px-2 py-1 text-center w-16">कैफियत</th>
                </tr>
            </thead>

            <tbody>
                @php
                    $total = 0;
                    $rowCount = count($items) ?: 1;
                @endphp

                @forelse($items as $i => $row)
                    @php
                        $amt = (float) str_replace(',', '', $row['amount'] ?? 0);
                        $total += $amt;
                    @endphp
                    <tr class="align-top">
                        <td class="border border-black text-center">{{ $row['sn'] }}</td>
                        <td class="border border-black px-2 break-words">{{ $row['desc'] }}</td>
                        <td class="border border-black text-center">{{ $row['unit'] ?? '—' }}</td>
                        <td class="border border-black text-right">{{ $row['min_stock'] ?? '—' }}</td>
                        <td class="border border-black text-right">{{ $row['store_bal'] ?? '—' }}</td>
                        <td class="border border-black text-right">{{ $row['required'] ?? '—' }}</td>
                        <td class="border border-black text-right">{{ $row['amount'] ?? '—' }}</td>
                        <td class="border border-black text-center">{{ $row['budget'] ?? '—' }}</td>

                        @if ($i === 0)
                            <td rowspan="{{ $rowCount + 1 }}" class="border border-black p-0">
                                <div class="h-full w-full flex items-center justify-center text-[13px] font-medium text-gray-800 px-1">
                                    {{ $slip->remarks ?? '—' }}
                                </div>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-8 text-gray-600">
                            कुनै सामाग्री सूचीबद्ध गरिएको छैन ।
                        </td>
                    </tr>
                @endforelse

                @if (count($items))
                    <tr class="font-semibold">
                        <td colspan="6" class="border border-black text-right px-3 py-2">जम्मा रकम</td>
                        <td class="border border-black text-right px-3 py-2">{{ number_format($total, 2) }}</td>
                        <td colspan="2" class="border border-black"></td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <!-- Footer -->
    <footer class="mt-auto pt-8">
        <div class="flex justify-between text-sm mt-10">
            <div class="text-center w-1/3">
                <p class="text-left text-[13px] font-medium mb-4">बजेटले भ्याउने / नभ्याउने</p>
                <div class="mt-6 leading-tight">
                    .........................................<br>लेखा
                </div>
            </div>

            <div class="text-center w-1/3">
                <div class="mt-12 leading-tight">
                    .........................................<br>सिफारिस गर्ने
                </div>
            </div>

            <div class="text-center w-1/3">
                <div class="mt-12 leading-tight">
                    .........................................<br>स्वीकृत गर्ने
                </div>
            </div>
        </div>

        <div class="text-xs text-gray-700 mt-8 border-t border-gray-300 pt-3 leading-relaxed">
            <strong>द्रष्टव्य:</strong> सामानको लागि माग फाराम भर्ना आवश्यक भएको कारण स्पष्ट खुलाउनु होला।
        </div>
    </footer>
</div>
@endsection
