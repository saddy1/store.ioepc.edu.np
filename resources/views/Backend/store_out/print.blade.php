@extends('Backend.layouts.app')

@section('content')
<style>
    @page { size: A4 portrait; margin: 12mm 12mm; }

    body{
        font-family:"Noto Sans Devanagari","Mangal","Kalimati",Arial,sans-serif;
        color:#111;
    }

    /* Screen Mode */
    @media screen{
        body{ background:#f3f4f6; padding:20px; }
        .printbar{
            display:flex; justify-content:space-between; align-items:center;
            gap:10px; flex-wrap:wrap; margin-bottom:12px;
        }
        .printbar a, .printbar button{
            padding:8px 14px; border:1px solid #d1d5db; border-radius:8px;
            background:#fff; font-size:14px;
        }
        .printbar a:hover, .printbar button:hover{ background:#f9fafb; }
        .paper{
            background:#fff; border-radius:10px; box-shadow:0 4px 16px rgba(0,0,0,.08);
            padding:10px;
        }
    }

    /* Print Mode */
    @media print{
        .printbar, header, nav, aside, .sidebar, .navbar, .app-header, .app-footer {
            display:none!important;
        }
        body{ background:#fff!important; padding:0!important; }
        .paper{ box-shadow:none!important; border-radius:0!important; padding:0!important; }
    }

    .paper{ width:100%; }

    /* Helpers */
    .row{ display:flex; justify-content:space-between; align-items:flex-end; }
    .tcenter{ text-align:center; }
    .tright{ text-align:right; }

    .small{ font-size:12px; }
    .xs{ font-size:11px; }
    .bold{ font-weight:700; }

    .hr{ border-top:1px solid #000; margin:6px 0 10px; }

    .dots{
        display:inline-block;
        border-bottom:1px dotted #000;
        min-width:170px; height:14px;
        vertical-align:bottom;
    }
    .dots.sm{ min-width:120px; }

    /* Table */
    table.form{
        width:100%; border-collapse:collapse; table-layout:fixed; margin-top:8px;
    }
    table.form th, table.form td{
        border:1px solid #000; padding:4px 5px; font-size:12px;
        word-break:break-word; vertical-align:top;
    }
    table.form th{ font-weight:700; text-align:center; }

    /* Signature Grid */
    .sign-grid{
        display:grid; grid-template-columns:repeat(3,1fr);
        gap:20px; margin-top:50px; font-size:12px;
    }
    .sign{
        border-top:0.5px solid #000;
        padding-top:14px; text-align:center;
    }

    /* Stamp Number */
    .red-no{
        color:#c50000; font-weight:800; font-size:18px; letter-spacing:1px;
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

    {{-- CENTER HEADER --}}
     <div class="tright">
        <div class="xs">वि. वि. पा. नं. १९(क)</div>
        
    </div>
    <div class="tcenter" style="margin-top:6px;">
        <div class="bold">त्रिभुवन विश्वविद्यालय</div>
        <div class="bold">पूर्वाञ्चल क्याम्पस, धरान</div>
        <div class="bold" style="margin-top:2px;">स्टोर माग तथा खर्च नोट</div>
    </div>

    {{-- RED SERIAL TOP RIGHT --}}
   

    {{-- METADATA ROW --}}
    <div class="row small" style="margin-top:10px;">
        <div>
            मिति :
            {{ $storeOut->store_out_date_bs ?? $storeOut->out_date_bs ?? $storeOut->store_out_date ?? $storeOut->out_date ?? '' }}
        </div>
        <div class="tright">
            स्टोर खर्च नं. :
            <span class="red-no">
            {{ $storeOut->store_out_sn ?? $storeOut->out_sn ?? '—' }}
            </span>
        </div>
    </div>

    <div class="hr"></div>

    {{-- LINES --}}
    <div class="small">श्री स्टोर , </div>
    <div class="small" style="margin-top:6px;">
        निम्न माल स्टोरबाट इश्यु गरि श्री
        <span class="bold">{{ $storeOut->employee?->full_name ?? $storeOut->employee_name ?? '' }}</span>
        को नाममा खर्च लेख्नु हुन अनुरोध छ ।
    </div>

    {{-- RECOMMENDATION + SIGN --}}
    <div class="small" style="margin-top:8px;">
        सिफारिस गर्ने : <span class="dots"></span>  <div class=" text-end">हस्ताक्षर :..........................</div>
    </div>

    {{-- TABLE --}}
    <table class="form">
        <thead>
        <tr>
            <th>सि.नं.</th>
            <th>मालको विवरण</th>
            <th>एकाई</th>
            <th>पहिले लिएको</th>
            <th>माग</th>
            <th>इश्यु</th>
            <th>पुनःगत</th>
            <th>खर्च खाता</th>
            <th>कैफियत</th>
        </tr>
        </thead>

        <tbody>
        @php $items = $storeOut->items ?? collect(); @endphp

        @forelse($items as $i => $it)
            @php
                $e = $it->storeEntryItem ?? null;
                $name = $it->item_name ?? $e?->item_name ?? '—';
                $sn   = $it->item_sn ?? $e?->item_sn ?? null;
                $unit = $it->unit ?? $e?->unit ?? '—';
                $qty  = (float)($it->qty ?? 0);
                $remark = $it->remarks ?? $storeOut->remarks ?? '';
            @endphp
            <tr>
                <td class="tcenter">{{ $i+1 }}</td>
                <td>{{ $name }} @if($sn)<div class="xs">({{ $sn }})</div>@endif</td>
                <td class="tcenter">{{ $unit }}</td>
                <td class="tcenter">—</td>
                <td class="tcenter">{{ number_format($qty,3) }}</td>
                <td class="tcenter">{{ number_format($qty,3) }}</td>
                <td class="tcenter">—</td>
                <td class="tcenter">—</td>
                <td class="xs">{{ $remark }}</td>
            </tr>
        @empty
            <tr><td colspan="9" class="tcenter" style="padding:20px;">हाल कुनै विवरण छैन</td></tr>
        @endforelse
        </tbody>
    </table>

    {{-- SIGNATURE BLOCK --}}
    <div class="sign-grid">
        <div class="sign">स्वीकृति दिने<br>(प्रमुख)</div>
        <div class="sign">माल इश्यु गर्ने<br>(स्टोर कर्मचारी)</div>
        <div class="sign">माल प्राप्त गर्ने<br>({{ $storeOut->employee?->full_name ?? $storeOut->employee_name ?? '' }})</div>
    </div>

    <div class="xs" style="margin-top:6px;">
        <span class="bold">सूचना :</span> दोस्रो प्रति स्टोरबाट प्रमाणित गरी सम्बन्धित इकाइलाई पठाउनु पर्नेछ ।
    </div>
</div>
@endsection
