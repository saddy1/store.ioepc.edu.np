@extends('Backend.layouts.app')
@section('content')
    <style>
        /* ----------- PRINT (A4 Landscape) ----------- */
        @page {
            size: A4 landscape;
            margin: 14mm 12mm;
        }

        /* ----------- BASE STYLES ----------- */
        body {
            font-family: "Noto Sans Devanagari", "Mangal", "Kalimati", Arial, sans-serif;
            color: #111;
            overflow-x: hidden;
        }

        .a4 {
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
            background: #fff;
            padding: 1rem;
            box-sizing: border-box;
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
                padding: 16px;
                box-shadow: 0 4px 20px rgba(0, 0, 0, .1);
                border-radius: 8px;
                overflow-x: auto;
                /* ✅ allows horizontal scroll if table overflows */
            }

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
                white-space: nowrap;
            }

            .printbar a:hover,
            .printbar button:hover {
                background: #f3f4f6;
            }
        }

        /* ----------- PRINT ONLY ----------- */
        @media print {

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

            body * {
                visibility: hidden !important;
            }

            #printable,
            #printable * {
                visibility: visible !important;
            }

            body {
                padding: 0 !important;
                background: #fff !important;
                overflow: visible !important;
            }

            #printable {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                margin: 0 !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                padding: 0 !important;
            }

            footer {
                position: fixed;
                bottom: 14mm;
                left: 0;
                right: 0;
            }
        }

        /* ----------- TABLE ----------- */
        table.ledger {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto;
        }

        table.ledger th,
        table.ledger td {
            border: 1px solid #000;
            padding: 4px 6px;
            font-size: 12px;
            line-height: 1.3;
            word-break: break-word;
        }

        .tcenter {
            text-align: center;
        }

        .tright {
            text-align: right;
        }

        .small {
            font-size: 11px;
        }
    </style>

    <div class="printbar">
        <div class="text-sm text-gray-600">
            <a href="{{ route('store.index') }}">← Store Entries</a>
        </div>
        <div class="flex gap-2 flex-wrap items-center">
            <form method="GET" class="flex gap-2 items-center flex-wrap">
                <input type="date" name="from" value="{{ request('from') }}" class="rounded border px-2 py-1 text-sm">
                <input type="date" name="to" value="{{ request('to') }}" class="rounded border px-2 py-1 text-sm">
                <button class="rounded bg-blue-600 text-white px-3 py-1.5 text-sm">Filter</button>
            </form>
            <button onclick="window.print()">🖨️ Print</button>
        </div>
    </div>

    <div id="printable" class="a4">
        <div class="tcenter mb-4">
            <div class="small">त्रिभुवन विश्वविद्यालय</div>
            <div style="font-weight:700;">पूर्वाञ्चल क्याम्पस, धरान</div>
            <div style="font-weight:700; margin-top:4px;">स्टोर श्रेणीगत खाता (Category Ledger)</div>
            <div class="text-2xl text-left" style="margin-top:4px;">
                श्रेणी: <b>{{ $meta['category_name'] }}</b>
                @if ($meta['from'] || $meta['to'])
                    — अवधि: <b>{{ $meta['from'] ?? '…' }}</b> देखि <b>{{ $meta['to'] ?? '…' }}</b>
                @endif
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="ledger min-w-max">
                <thead>
                    <tr>
                        <th colspan="8" class="tcenter" style="width:40px;">आम्दानी </th>
                        <th colspan="5" class="tcenter" style="width:40px;">kharcha </th>
                        <th colspan="5" class="tcenter" style="width:40px;">Baki </th>

                    </tr>


                    <tr>

                        <td colspan="2" class="tcenter" style="width:110px;">खरिद आदेश</td>
                        <td rowspan="2" class="tcenter" style="min-width:180px;">सप्लायर्स </td>
                        <td colspan="2" class="tcenter" style="width:110px;">स्टोर प्राप्ति</th>
                        <td rowspan="2" class="tcenter" style="width:70px;">परिमाण</td>
                        <td rowspan="2" class="tcenter" style="width:90px;">एकाई दर</td>


                        <td rowspan="2" class="tcenter" style="width:90px;">रकम</td>
                        <th colspan="2" class="tcenter" style="width:70px;">खर्च नोट </td>
                        <td rowspan="2" class="tcenter" style="width:70px;">मालसामान पठाएको स्थान </td>
                        <th rowspan="2" class="tcenter" style="width:70px;">परिमाण</td>
                        <td rowspan="2" class="tcenter" style="width:90px;">रकम</td>
                        <td rowspan="2" class="tcenter" style="width:90px;">मौज्दात परिमाण </td>
                        <td rowspan="2" class="tcenter" style="width:90px;">रकम</td>

                        <td rowspan="2" class="tcenter" style="width:150px;">कैफियत</td>

                    </tr>
                    <tr>
                        <td class="tcenter" style="width:70px;">क्रमांक</td>
                        <td class="tcenter" style="width:80px;">मिति</td>
                        <td class="tcenter" style="width:70px;">क्रमांक</td>
                        <td class="tcenter" style="width:70px;">मिति</td>
                        <td class="tcenter" style="width:70px;">क्रमांक</td>

                        <td class="tcenter" style="width:70px;">मिति</td>



                    </tr>
                </thead>

                <tbody>


                    @forelse($rows as $r)
                        <tr>
                            <td class="tcenter">{{ $r['slip_sn'] }}</td>
                            <td class="tcenter small"><br>{{ $r['slip_date'] }}</td>
                            <td class="small text-center " style="width: 120px">{{ $r['supplier'] }} <br>( {{ $r['desc'] }})</td>
                            <td class="tcenter small">{{ $r['purchase_sn'] }}</td>
                            
                            
                            <td class="tcenter">{{ $r['purchase_date'] }}</td>
                            <td class="tcenter">{{ $r['qty'] }}({{ $r['unit']}})</td>
                            <td class="tright">{{ $r['rate'] }}</td>
                            <td class="tright">{{ $r['amount'] }}</td>
                            {{-- <td class="tcenter">{{ $r['ledger'] ?: '' }}</td> --}}
                            {{-- <td class="small">{{ $r['remarks'] }}</td> --}}
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="tcenter py-4">हाल कुनै अभिलेख छैन</td>
                        </tr>
                    @endforelse

                    @if (count($rows))
                        <tr>
                            <td colspan="7" class="tright font-bold">जम्मा</td>
                            <td class="tright font-bold">{{ $meta['grand_total'] }}</td>
                            <td colspan="2"></td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <footer class="mt-6">
            <div class="small text-gray-700">टिप्पणी: यो फर्म उत्पाद श्रेणी (Product Category) अनुसार Store Entry बाट
                स्वचालित रूपमा बनेको हो।</div>
        </footer>
    </div>
@endsection
