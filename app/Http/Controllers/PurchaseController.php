<?php

namespace App\Http\Controllers;

use App\Models\{
    Purchase,
    PurchaseItem,
    PurchaseSlip,
    PurchaseSlipItem,
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
            ->with([
                'supplier',
                'department',
                'storeEntry',
                'items.slipItem.slip', // ✅ multi-slip display
            ])
            ->latest('purchase_date');

        if ($s = trim((string) $request->get('search'))) {
            $q->where(function ($w) use ($s) {
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) {
                    $w->whereDate('purchase_date', $s);
                } else {
                    $w->where('purchase_sn', 'like', "%{$s}%")
                      ->orWhere('bill_no', 'like', "%{$s}%");
                }

                $w->orWhereHas('supplier', fn ($x) => $x->where('name', 'like', "%{$s}%"))
                  ->orWhereHas('items.slipItem.slip', fn ($x) => $x->where('po_sn', 'like', "%{$s}%"));
            });
        }

        $orders = $q->paginate(10)->appends($request->only('search'));

        return view('Backend.purchases.index', compact('orders'));
    }

    public function create(Request $request)
    {
        return view('Backend.purchases.create', [
            'suppliers'   => Supplier::orderBy('name')->get(),
            'departments' => Department::orderBy('name')->get(),
            'products'    => Product::orderBy('name')->get(),
        ]);
    }

    public function show(Purchase $purchase)
    {
        $purchase->load([
            'supplier',
            'department',
            'storeEntry',
            'items.product',
            'items.slipItem.slip', // ✅ multi-slip for printing
        ]);

        $items = $purchase->items->values()->map(function ($line, $idx) {
            $name = $line->temp_name ?: ($line->product->name ?? '—');
            $unit = $line->unit ?: ($line->product->unit ?? '');

            return [
                'sn'             => $idx + 1,
                'desc'           => $name,
                'unit'           => $unit,
                'qty'            => number_format((float) $line->qty, 3),
                'rate'           => number_format((float) $line->rate, 2),
                'amount'         => number_format((float) ($line->line_subtotal ?? ((float)$line->qty * (float)$line->rate)), 2),

                // Multi-slip hint (optional to show in print)
                'po_sn'          => $line->slipItem?->slip?->po_sn,
                'po_date'        => optional($line->slipItem?->slip?->po_date)->format('Y-m-d'),
                'store_entry_sn' => $line->store_entry_sn ?? '—',
                'remark'         => $line->notes ?? '',
            ];
        });

        $meta = [
            'bill_no'       => $purchase->bill_no,
            'purchase_sn'   => $purchase->purchase_sn,
            'purchase_date' => $purchase->purchase_date,
            'supplier'      => $purchase->supplier->name ?? '—',
            'dept'          => $purchase->department->name ?? '—',

            'tax_mode'      => $purchase->tax_mode ?? 'PAN',
            'vat_percent'   => $purchase->tax_mode === 'VAT' ? (float) $purchase->vat_percent : 0.0,
            'sub_total'     => number_format((float) $purchase->sub_total, 2),
            'vat_amount'    => number_format((float) $purchase->vat_amount, 2),
            'grand_total'   => number_format((float) ($purchase->grand_total ?: $purchase->total_amount), 2),

            'remarks'       => $purchase->remarks ?? '—',
        ];

        return view('Backend.purchases.show_print', compact('purchase', 'items', 'meta'));
    }

  public function store(Request $request)
{
    $rules = [
        'purchase_sn'      => ['required', 'string', 'max:50', 'unique:purchases,purchase_sn'],
        'purchase_date'    => ['required', 'date'],
        'supplier_id'      => ['nullable', 'exists:suppliers,id'],
        'department_id'    => ['nullable', 'exists:departments,id'],
        'remarks'          => ['nullable', 'string', 'max:2000'],

        'tax_mode'         => ['nullable', 'in:VAT,PAN'],
        'vat_percent'      => ['nullable', 'numeric', 'gte:0', 'lte:100'],
        'bill_no'          => ['required', 'string', 'max:100'],

        'items'            => ['required', 'array', 'min:1'],

        // ✅ ADD THIS (so it is not stripped by validate())
        'items.*.purchase_slip_item_id' => ['nullable', 'exists:purchase_slip_items,id'],

        'items.*.name'     => ['required', 'string', 'max:255'],
        'items.*.qty'      => ['required', 'numeric', 'gt:0'],
        'items.*.rate'     => ['required', 'numeric', 'gte:0'],
        'items.*.unit'     => ['required', 'string', 'max:20'],
        'items.*.item_category_id' => ['nullable', 'exists:item_categories,id'],
        'items.*.temp_sn'  => ['nullable', 'string', 'max:100'],
    ];

    $data = $request->validate($rules);

    DB::transaction(function () use ($data) {

        $purchase = Purchase::create([
            'purchase_sn'   => $data['purchase_sn'],
            'purchase_date' => $data['purchase_date'],
            'supplier_id'   => $data['supplier_id'] ?? null,
            'department_id' => $data['department_id'] ?? null,
            'remarks'       => $data['remarks'] ?? null,
            'tax_mode'      => $data['tax_mode'] ?? 'PAN',
            'bill_no'       => $data['bill_no'],
            'vat_percent'   => $data['vat_percent'] ?? 13.00,
            'sub_total'     => 0,
            'vat_amount'    => 0,
            'grand_total'   => 0,
            'total_amount'  => 0,
        ]);

        $bulk = [];

        foreach ($data['items'] as $row) {

            $slipItemId = $row['purchase_slip_item_id'] ?? null;

            if ($slipItemId) {
                $slipItem = \App\Models\PurchaseSlipItem::lockForUpdate()->findOrFail($slipItemId);

                // ❗ allow user to change name + qty (your request)
                // qty cannot exceed ordered_qty (or remaining if you track partials)
                $qty  = (float)($row['qty'] ?? 0);
                $rate = (float)($row['rate'] ?? 0);

                if ($qty <= 0) {
                    throw ValidationException::withMessages(['items' => ['Qty must be > 0.']]);
                }

                if ($qty > (float)$slipItem->ordered_qty) {
                    throw ValidationException::withMessages([
                        'items' => ["Qty cannot exceed ordered qty for '{$slipItem->temp_name}'."],
                    ]);
                }

                if ($rate > (float)$slipItem->max_rate) {
                    throw ValidationException::withMessages([
                        'items' => ["Rate exceeds max_rate for '{$slipItem->temp_name}'."],
                    ]);
                }

                $bulk[] = new PurchaseItem([
                    // ✅ store references
                    'purchase_slip_item_id' => $slipItem->id,
                    'purchase_slip_id'      => $slipItem->purchase_slip_id,

                    'product_id'            => null,
                    'item_category_id'      => $slipItem->item_category_id,

                    // ✅ allow override name/sn/unit from form (old-project behavior)
                    'temp_name'             => $row['name'] ?? $slipItem->temp_name,
                    'temp_sn'               => $row['temp_sn'] ?? $slipItem->temp_sn,
                    'unit'                  => $row['unit'] ?? $slipItem->unit,

                    'qty'                   => $qty,
                    'rate'                  => $rate,
                    'discount_percent'      => 0,
                    'discount_amount'       => 0,
                    'line_subtotal'         => round($qty * $rate, 2),
                ]);

            } else {
                // Manual/Tender item
                $qty  = (float)$row['qty'];
                $rate = (float)$row['rate'];

                $bulk[] = new PurchaseItem([
                    'purchase_slip_item_id' => null,
                    'purchase_slip_id'      => null,
                    'product_id'            => null,
                    'item_category_id'      => $row['item_category_id'] ?? null,
                    'temp_name'             => $row['name'],
                    'temp_sn'               => $row['temp_sn'] ?? null,
                    'unit'                  => $row['unit'] ?? null,
                    'qty'                   => $qty,
                    'rate'                  => $rate,
                    'discount_percent'      => 0,
                    'discount_amount'       => 0,
                    'line_subtotal'         => round($qty * $rate, 2),
                ]);
            }
        }

        $purchase->items()->saveMany($bulk);

        $purchase->load('items');
        $purchase->recomputeTotals();
        $purchase->save();
    });

    return redirect()->route('purchases.index')->with('success', 'Purchase saved.');
}


    public function edit(Purchase $purchase)
    {
        if ($purchase->storeEntry) {
            return redirect()
                ->route('purchases.index')
                ->with('error', 'This purchase is already posted to store and cannot be edited.');
        }

        $purchase->load([
            'items.product',
            'items.slipItem.slip',
            'supplier',
            'department',
        ]);

        return view('Backend.purchases.edit', [
            'purchase'    => $purchase,
            'suppliers'   => Supplier::orderBy('name')->get(),
            'departments' => Department::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Purchase $purchase)
    {
        if ($purchase->storeEntry) {
            return redirect()
                ->route('purchases.index')
                ->with('error', 'This purchase is already posted to store and cannot be edited.');
        }

        $rules = [
            'purchase_sn'   => ['required', 'string', 'max:50', Rule::unique('purchases', 'purchase_sn')->ignore($purchase->id)],
            'purchase_date' => ['required', 'date'],
            'supplier_id'   => ['required', 'exists:suppliers,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'remarks'       => ['nullable', 'string', 'max:2000'],

            'tax_mode'      => ['nullable', 'in:PAN,VAT'],
            'vat_percent'   => ['nullable', 'numeric', 'gte:0', 'lte:100'],
            'bill_no'       => ['required', 'string', 'max:100'],

            'items'                     => ['required', 'array', 'min:1'],
            'items.*.purchase_slip_item_id' => ['nullable', 'exists:purchase_slip_items,id'],
            'items.*.name'              => ['required', 'string', 'max:255'],
            'items.*.qty'               => ['required', 'numeric', 'gt:0'],
            'items.*.rate'              => ['required', 'numeric', 'gte:0'],
            'items.*.unit'              => ['nullable', 'string', 'max:20'],
            'items.*.item_category_id'  => ['nullable', 'exists:item_categories,id'],
            'items.*.temp_sn'           => ['nullable', 'string', 'max:100'],
        ];

        $data = $request->validate($rules);

        DB::transaction(function () use ($data, $purchase) {
            $purchase->update([
                'purchase_sn'   => $data['purchase_sn'],
                'purchase_date' => $data['purchase_date'],
                'supplier_id'   => $data['supplier_id'],
                'department_id' => $data['department_id'] ?? $purchase->department_id,
                'remarks'       => $data['remarks'] ?? null,
                'tax_mode'      => $data['tax_mode'] ?? $purchase->tax_mode ?? 'PAN',
                'vat_percent'   => $data['vat_percent'] ?? $purchase->vat_percent ?? 13.00,
                'bill_no'       => $data['bill_no'],
            ]);

            // Replace items (NOTE: if you want to keep history, use soft-delete)
            $purchase->items()->delete();

            $lines = [];

            foreach ($data['items'] as $row) {
                $qty  = (float) $row['qty'];
                $rate = (float) $row['rate'];

                $lines[] = new PurchaseItem([
                    'purchase_slip_item_id' => $row['purchase_slip_item_id'] ?? null,
                    'product_id'            => null,
                    'item_category_id'      => $row['item_category_id'] ?? null,
                    'temp_name'             => $row['name'],
                    'temp_sn'               => $row['temp_sn'] ?? null,
                    'unit'                  => $row['unit'] ?? null,
                    'qty'                   => $qty,
                    'rate'                  => $rate,
                    'discount_percent'      => 0,
                    'discount_amount'       => 0,
                    'line_subtotal'         => round($qty * $rate, 2),
                    'notes'                 => null,
                ]);
            }

            $purchase->items()->saveMany($lines);

            $purchase->load('items');
            if (method_exists($purchase, 'recomputeTotals')) {
                $purchase->recomputeTotals();
            } else {
                $sub = (float) $purchase->items->sum('line_subtotal');
                $vat = ($purchase->tax_mode === 'VAT')
                    ? round($sub * (((float) $purchase->vat_percent) / 100), 2)
                    : 0;

                $purchase->sub_total    = round($sub, 2);
                $purchase->vat_amount   = round($vat, 2);
                $purchase->grand_total  = round($sub + $vat, 2);
                $purchase->total_amount = $purchase->grand_total;
            }

            $purchase->save();
        });

        return redirect()->route('purchases.index')->with('success', 'Purchase updated.');
    }

    public function destroy(Purchase $purchase)
    {
        $purchase->items()->delete();
        $purchase->delete();

        return back()->with('success', 'Purchase deleted.');
    }
}
