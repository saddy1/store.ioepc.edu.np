@extends('Backend.layouts.app')

@section('content')
<style>
    @page { size: A4 portrait; margin: 12mm 12mm; }

    body{
        font-family:"Noto Sans Devanagari","Mangal","Kalimati",Arial,sans-serif;
        color:#111;
    }

    /* Screen wrapper */
    @media screen{
        body{ background:#f3f4f6; padding:20px; }
        .printbar{
            display:flex; justify-content:space-between; align-items:center; gap:10px;
            flex-wrap:wrap; margin-bottom:12px;
        }
        .printbar a,.printbar button{
            padding:8px 14px; border:1px solid #d1d5db; border-radius:8px;
            background:#fff; font-size:14px;
        }
        .printbar a:hover,.printbar button:hover{ background:#f9fafb; }
        .paper{
            background:#fff; border-radius:10px; box-shadow:0 4px 16px rgba(0,0,0,.08);
            padding:10px;
        }
    }

    /* Print */
    @media print{
        .printbar, header, nav, aside, .sidebar, .navbar, .app-header, .app-footer { display:none!important; }
        body{ background:#fff!important; padding:0!important; }
        .paper{ box-shadow:none!important; border-radius:0!important; padding:0!important; }
    }

    .paper{ width:100%; }

    .row{ display:flex; justify-content:space-between; align-items:flex-end; gap:8px; }
    .tcenter{ text-align:center; }
    .tright{ text-align:right; }

    .small{ font-size:12px; }
    .xs{ font-size:11px; }
    .bold{ font-weight:700; }

    .hr{ border-top:1px solid #000; margin:6px 0 10px; }

    .dots{
        display:inline-block;
        border-bottom:1px dotted #000;
        min-width:170px;
        height:14px;
        vertical-align:bottom;
    }
    .dots.sm{ min-width:120px; }
    .dots.lg{ min-width:260px; }

    /* Table like paper: very thin and fixed */
    table.form{
        width:100%;
        border-collapse:collapse;
        table-layout:fixed;
        margin-top:8px;
    }
    table.form th, table.form td{
        border:1px solid #000;
        padding:4px 5px;
        font-size:12px;
        vertical-align:top;
        word-break:break-word;
    }
    table.form th{
        font-weight:700;
        text-align:center;
    }

    /* Bottom area exactly like paper */
    .bottom-area{
        margin-top:10px;
        font-size:12px;
    }
    .bottom-row{
        display:flex;
        justify-content:space-between;
        gap:10px;
        margin-top:6px;
        flex-wrap:wrap;
    }
    .bottom-row .col{
        min-width: 250px;
    }

    .sign-grid{
        display:grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 12px;
        margin-top: 18px;
        font-size: 12px;
    }
    .sign{
        border-top:1px solid #000;
        padding-top:14px;
        text-align:center;
    }

    .note{
        margin-top:10px;
        font-size:11px;
    }

    /* Red number (top-right) like stamp */
    .red-no{
        color:#c50000;
        font-weight:800;
        font-size:18px;
        letter-spacing:1px;
    }
</style>

<div class="printbar">
    <a href="{{ route('store.out.index') }}">← Store Out List</a>
    <div class="flex gap-2">
        <a href="{{ route('store.out.show', $storeOut) }}">Back to Detail</a>
        <button onclick="window.print()">🖨️ Print</button>
    </div>
</div>

<div class="paper">

    {{-- TOP LINE (same like photo) --}}
    <div class="row small">
        <div>मिति : <span class="dots sm">
            {{ $storeOut->store_out_date_bs ?? $storeOut->out_date_bs ?? $storeOut->store_out_date ?? $storeOut->out_date ?? '' }}
        </span></div>

        <div class="tright">
            क्रम संख्या : <span class="dots sm"></span>
        </div>
    </div>

    {{-- HEADER CENTER --}}
    <div class="row" style="margin-top:6px;">
        <div class="tcenter" style="flex:1;">
            <div class="bold">त्रिभुवन विश्वविद्यालय</div>
            <div class="small">.......................... क्याम्पस / कार्यालय</div>
            <div class="bold" style="margin-top:2px;">स्टोर माग तथा खर्च नोट</div>
        </div>

        {{-- right top red serial no like pic --}}
        <div class="tright" style="min-width:160px;">
            <div class="xs">वि. वि. पा. नं. १८(क)</div>
            <div class="red-no">
                {{ $storeOut->store_out_sn ?? $storeOut->out_sn ?? '—' }}
            </div>
        </div>
    </div>

    <div class="hr"></div>

    {{-- BODY LINES (like photo, very minimal) --}}
    <div class="small">
        श्री स्टोर <span class="dots lg"></span>
    </div>

    <div class="small" style="margin-top:6px;">
        निम्न माल स्टोरबाट इश्यु गरि <span class="dots lg"></span>
        मा खर्च लेख्नु हुन अनुरोध छ ।
    </div>

    {{-- TABLE --}}
    <table class="form">
        <thead>
        <tr>
            <th style="width:5%;">सि.नं.</th>
            <th style="width:30%;">मालको विवरण</th>
            <th style="width:7%;">एकाई</th>
            <th style="width:13%;">यस अघि लिएको मिति र परिमाण</th>
            <th style="width:10%;">माग भएको परिमाण</th>
            <th style="width:10%;">इश्यु भएको परिमाण</th>
            <th style="width:10%;">पुनःगत स्टोर खाता नं.</th>
            <th style="width:7%;">खर्च स्टोर खाता नं.</th>
            <th style="width:8%;">कैफियत</th>
        </tr>
        </thead>

        <tbody>
        @php $items = $storeOut->items ?? collect(); @endphp

        @forelse($items as $i => $it)
            @php
                $entry = $it->storeEntryItem ?? null;
                $name  = $it->item_name ?? $entry?->item_name ?? '—';
                $sn    = $it->item_sn ?? $entry?->item_sn ?? null;
                $unit  = $it->unit ?? $entry?->unit ?? '—';
                $qty   = (float)($it->qty ?? 0);
                $remark = $it->remarks ?? $storeOut->remarks ?? '';
            @endphp
            <tr>
                <td class="tcenter">{{ $i+1 }}</td>
                <td>
                    {{ $name }}
                    @if($sn)
                        <div class="xs">( {{ $sn }} )</div>
                    @endif
                </td>
                <td class="tcenter">{{ $unit }}</td>
                <td class="tcenter">—</td>
                <td class="tcenter">{{ number_format($qty, 3) }}</td>
                <td class="tcenter">{{ number_format($qty, 3) }}</td>
                <td class="tcenter">—</td>
                <td class="tcenter">—</td>
                <td class="xs">{{ $remark }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="tcenter" style="padding:20px;">हाल कुनै विवरण छैन</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    {{-- BOTTOM AREA (put sifaris and signature like photo bottom) --}}
    <div class="bottom-area">
        <div class="bottom-row">
            <div class="col">
                सिफारिस गर्ने <span class="dots">
                    {{ $storeOut->employee?->full_name ?? $storeOut->employee_name ?? '' }}
                </span>
            </div>
            <div class="col tright">
                हस्ताक्षर <span class="dots"></span>
            </div>
        </div>
    </div>

    {{-- SIGNATURES (same 3 boxes at bottom like your paper) --}}
    <div class="sign-grid">
        <div class="sign">
            स्वीकृति दिने<br>(प्रमुख)
        </div>
        <div class="sign">
            माल इश्यु गर्ने<br>(स्टोर कर्मचारी)
        </div>
        <div class="sign">
            माल प्राप्त गर्ने<br>(प्राप्तकर्ता)
        </div>
    </div>

    <div class="note">
        <span class="bold">सूचना :</span>
        दोस्रो प्रति स्टोरबाट प्रमाणित गरी सम्बन्धित इकाइलाई पठाउनु पर्नेछ । यसको आधारमा सम्बन्धित इकाइले लेखा राख्नु पर्नेछ ।
    </div>

</div>
@endsection
