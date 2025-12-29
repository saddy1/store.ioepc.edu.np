<?php

namespace App\Http\Controllers;

use App\Models\{
    Purchase,
    PurchaseItem,
    PurchaseSlip,
    Supplier,
    Department,
    Product
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $q = Purchase::query()
            ->with(['supplier','slip','department'])
            ->latest('purchase_date');

        if ($s = trim((string) $request->get('search'))) {
            // Group search to avoid precedence bugs
            $q->where(function ($w) use ($s) {
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) {
                    $w->whereDate('purchase_date', $s);
                } else {
                    $w->where('purchase_sn', 'like', "%{$s}%");
                }

                $w->orWhereHas('supplier', fn($x) => $x->where('name','like',"%{$s}%"))
                  ->orWhereHas('slip', fn($x)    => $x->where('po_sn','like',"%{$s}%"));
            });
        }

        $orders = $q->paginate(10)->appends($request->only('search'));

        return view('Backend.purchases.index', compact('orders'));
    }

  public function show(Purchase $purchase)
{
    // Load all relations we need
    $purchase->load(['supplier','department','slip','items.product']);

    // Build printable rows
    $items = $purchase->items->values()->map(function ($line, $idx) {
        $name = $line->temp_name ?: ($line->product->name ?? '—');
        $unit = $line->unit ?: ($line->product->unit ?? '');
        $qty  = (float) $line->qty;
        $rate = (float) $line->rate;

        return [
            'sn'        => $idx + 1,
            'desc'      => $name,
            'unit'      => $unit,
            'store_entry_sn' => $line->store_entry_sn ?? '—',
            'required'  => number_format($qty, 3),             // quantity
            'rate'      => number_format($rate, 2),            // unit price
            'amount'    => number_format($line->line_subtotal ?? ($qty * $rate), 2),
            'remark'    => $line->notes ?? '',                 // per-line remark if you use it
        ];
    });

    // Header/meta for the print header
    $meta = [
        'bill_no'       => $purchase->bill_no,
        'purchase_sn'   => $purchase->purchase_sn,
        'purchase_date' => $purchase->purchase_date,
        'supplier'      => $purchase->supplier->name ?? '—',
        'dept'          => $purchase->department->name
                           ?? $purchase->slip?->department?->name
                           ?? '—',
        'tax_mode'      => $purchase->tax_mode ?? 'PAN',
        'vat_percent'   => $purchase->tax_mode === 'VAT' ? (float)$purchase->vat_percent : 0.0,
        'sub_total'     => number_format((float)$purchase->sub_total, 2),
        'vat_amount'    => number_format((float)$purchase->vat_amount, 2),
        'grand_total'   => number_format((float)($purchase->grand_total ?: $purchase->total_amount), 2),

        // Slip (optional)
        'slip_sn'       => $purchase->slip?->po_sn,
        'slip_date'     => optional($purchase->slip?->po_date)->format('Y-m-d'),
        'remarks'       => $purchase->remarks ?? '—',
    ];

    return view('Backend.purchases.show_print', compact('purchase','items','meta'));
}


    public function create(Request $request)
    {
        $slip = PurchaseSlip::with('items')->findOrFail($request->get('slip_id')); // must select a slip

        return view('Backend.purchases.create', [
            'slip'       => $slip,
            'suppliers'  => Supplier::orderBy('name')->get(),
            'departments'=> Department::orderBy('name')->get(),
            'products'   => Product::orderBy('name')->get(),
        ]);
    }

public function store(Request $request)
{
    // Slip is optional now
    $slipId = $request->input('purchase_slip_id');
    $slip   = $slipId ? PurchaseSlip::findOrFail($slipId) : null;

    // Build rules (date after-or-equal only if slip exists)
    $rules = [
        'purchase_sn'      => ['required','string','max:50','unique:purchases,purchase_sn'],
        'purchase_date'    => ['required','date'],
        'supplier_id'      => ['nullable','exists:suppliers,id'], // supplier optional if you want
        'purchase_slip_id' => ['nullable','exists:purchase_slips,id'],
        'department_id'    => ['nullable','exists:departments,id'],
        
        'remarks'          => ['nullable','string','max:2000'],

        'tax_mode'         => ['nullable','in:VAT,PAN'],
        'vat_percent'      => ['nullable','numeric','gte:0','lte:100'],
        'bill_no'         => ['required','string','max:100'],

        'items'                 => ['required','array','min:1'],
        'items.*.name'          => ['required','string','max:255'],
        'items.*.qty'           => ['required','numeric','gt:0'],
        'items.*.rate'          => ['required','numeric','gte:0'],
        'items.*.unit'          => ['required','string','max:20'],
        'items.*.item_category_id' => ['nullable','exists:item_categories,id'],
        'items.*.temp_sn'       => ['nullable','string','max:100'],
    ];

    if ($slip) {
        $rules['purchase_date'][] = 'after_or_equal:'.$slip->po_date->toDateString();
    }

    $data = $request->validate($rules);

    DB::transaction(function() use ($data, $slip) {
        $purchase = Purchase::create([
            'purchase_sn'      => $data['purchase_sn'],
            'purchase_date'    => $data['purchase_date'],
            'supplier_id'      => $data['supplier_id'] ?? null,
            'purchase_slip_id' => $data['purchase_slip_id'] ?? null,
            'department_id'    => $data['department_id'] ?? ($slip->department_id ?? null),
            'remarks'          => $data['remarks'] ?? null,

            'tax_mode'         => $data['tax_mode'] ?? 'PAN',
            'bill_no'         => $data['bill_no'],
            'vat_percent'      => $data['vat_percent'] ?? 13.00,
            'sub_total'        => 0,
            'vat_amount'       => 0,
            'grand_total'      => 0,
            'total_amount'     => 0,
        ]);

        $bulk = [];
        foreach ($data['items'] as $row) {
            $qty  = (float)$row['qty'];
            $rate = (float)$row['rate'];

            $bulk[] = new PurchaseItem([
                'product_id'       => null,                     // no mapping now
                'item_category_id' => $row['item_category_id'] ?? null,
                'temp_name'        => $row['name'],
                'temp_sn'          => $row['temp_sn'] ?? null,
                'unit'             => $row['unit'] ?? null,
                'qty'              => $qty,
                'rate'             => $rate,
                'discount_percent' => 0,
                'discount_amount'  => 0,
                'line_subtotal'    => round($qty * $rate, 2),
                'notes'            => null,
            ]);
        }

        $purchase->items()->saveMany($bulk);

        $purchase->load('items');
        $purchase->recomputeTotals();
        $purchase->save();
    });

    return redirect()->route('purchases.index')->with('success','Purchase saved.');
}




    public function edit(Purchase $purchase)
    {
         if ($purchase->storeEntry) {
        return redirect()
            ->route('purchases.index')
            ->with('error', 'This purchase is already posted to store and cannot be edited.');
    }
        $purchase->load(['items.product','supplier','department','slip.items']);
        return view('Backend.purchases.edit', [
            'purchase'   => $purchase,
            'suppliers'  => Supplier::orderBy('name')->get(),
            'departments'=> Department::orderBy('name')->get(),
        ]);
    }

public function update(Request $request, Purchase $purchase)
{
    // Slip is optional; if exists apply date constraint only
    $slip = $purchase->purchase_slip_id ? PurchaseSlip::find($purchase->purchase_slip_id) : null;

    $rules = [
        'purchase_sn'      => ['required','string','max:50', Rule::unique('purchases','purchase_sn')->ignore($purchase->id)],
        'purchase_date'    => ['required','date'],
        'supplier_id'      => ['nullable','exists:suppliers,id'],
        'department_id'    => ['nullable','exists:departments,id'],
        'remarks'          => ['nullable','string','max:2000'],

        'tax_mode'         => ['nullable','in:VAT,PAN'],
        'vat_percent'      => ['nullable','numeric','gte:0','lte:100'],

        'items'                 => ['required','array','min:1'],
        'items.*.name'          => ['required','string','max:255'],
        'items.*.qty'           => ['required','numeric','gt:0'],
        'items.*.rate'          => ['required','numeric','gte:0'],
        'items.*.unit'          => ['nullable','string','max:20'],
        'items.*.item_category_id' => ['nullable','exists:item_categories,id'],
        'items.*.temp_sn'       => ['nullable','string','max:100'],
    ];

    if ($slip) {
        $rules['purchase_date'][] = 'after_or_equal:'.$slip->po_date->toDateString();
    }

    $data = $request->validate($rules);

    DB::transaction(function () use ($data, $purchase) {
        $purchase->update([
            'purchase_sn'      => $data['purchase_sn'],
            'purchase_date'    => $data['purchase_date'],
            'supplier_id'      => $data['supplier_id'] ?? null,
            'department_id'    => $data['department_id'] ?? $purchase->department_id,
            'remarks'          => $data['remarks'] ?? null,
            'tax_mode'         => $data['tax_mode'] ?? $purchase->tax_mode ?? 'PAN',
            'vat_percent'      => $data['vat_percent'] ?? $purchase->vat_percent ?? 13.00,
        ]);

        // replace items
        $purchase->items()->delete();

        $bulk = [];
        foreach ($data['items'] as $row) {
            $qty  = (float)$row['qty'];
            $rate = (float)$row['rate'];

            $bulk[] = new PurchaseItem([
                'product_id'       => null,
                'item_category_id' => $row['item_category_id'] ?? null,
                'temp_name'        => $row['name'],
                'temp_sn'          => $row['temp_sn'] ?? null,
                'unit'             => $row['unit'] ?? null,
                'qty'              => $qty,
                'rate'             => $rate,
                'discount_percent' => 0,
                'discount_amount'  => 0,
                'line_subtotal'    => round($qty * $rate, 2),
                'notes'            => null,
            ]);
        }
        $purchase->items()->saveMany($bulk);

        $purchase->load('items');
        $purchase->recomputeTotals();
        $purchase->save();
    });

    return redirect()->route('purchases.index')->with('success','Purchase updated.');
}


    public function destroy(Purchase $purchase)
    {
        $purchase->items()->delete();
        $purchase->delete();
        return back()->with('success','Purchase deleted.');
    }
}
