<?php

/**
 * Edit a specific purchase slip.
 *
 * @param \App\Models\PurchaseSlip $slip
 */

namespace App\Http\Controllers;

use App\Models\{
    PurchaseSlip,
    PurchaseSlipItem,
    Department,
    PurchaseLine,
    ItemCategory
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;


class PurchaseSlipController extends Controller
{
    /** AJAX: suggest previously ordered slip items */
    public function productSearch(Request $request)
    {
        $q = trim((string)$request->get('q', ''));
        if ($q === '') {
            return response()->json([]);
        }

        // Pull recent matches by name or detail (temp_sn)
        $pool = PurchaseSlipItem::query()
            ->whereNotNull('temp_name')
            ->where(function ($x) use ($q) {
                $x->where('temp_name', 'like', "%{$q}%")
                    ->orWhere('temp_sn', 'like', "%{$q}%");
            })
            ->orderByDesc('created_at')
            ->limit(50)
            ->get([
                'id',
                'temp_name',
                'temp_sn',
                'ordered_qty',
                'max_rate',
                'unit',
                'item_category_id',
                'created_at'
            ]);

        // Unique by (name|sn) - case insensitive
        $unique = $pool->unique(function ($i) {
            return mb_strtolower(trim($i->temp_name ?? '')) . '|' . mb_strtolower(trim($i->temp_sn ?? ''));
        })->take(10)->values();

        // Preload categories
        $catIds = $unique->pluck('item_category_id')->filter()->unique()->values();
        $catMap = ItemCategory::whereIn('id', $catIds)->get()
            ->keyBy('id')
            ->map(fn($c) => $c->name_en . ' (' . $c->name_np . ')');

        return response()->json(
            $unique->map(function ($i) use ($catMap) {
                return [
                    'id'                 => $i->id,
                    'text'               => trim($i->temp_name . ($i->temp_sn ? " ({$i->temp_sn})" : "")),
                    'temp_name'          => $i->temp_name ?? '',
                    'temp_sn'            => $i->temp_sn ?? '',
                    'last_qty'           => (string)($i->ordered_qty ?? ''),
                    'last_rate'          => (string)($i->max_rate ?? ''),
                    'last_unit'          => (string)($i->unit ?? ''),
                    'last_category_id'   => $i->item_category_id,
                    'last_category_name' => $i->item_category_id ? ($catMap[$i->item_category_id] ?? '') : '',
                ];
            })
        );
    }

    public function index(Request $request)
    {
        $q = PurchaseSlip::query()
            ->with('department')
            ->withCount('purchases')
            ->latest('po_date');

        if ($s = trim((string)$request->get('search'))) {
            $q->where(function ($w) use ($s) {
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) {
                    $w->whereDate('po_date', $s);
                } else {
                    $w->where('po_sn', 'like', "%{$s}%");
                }
                $w->orWhereHas('department', fn($x) => $x->where('name', 'like', "%{$s}%"));
            });
        }

        $slips = $q->paginate(10)->appends($request->only('search'));
        return view('Backend.slips.index', compact('slips'));
    }

    public function create()
    {
        return view('Backend.slips.create', [
            'departments'   => Department::orderBy('name')->get(),
            'item_category' => ItemCategory::orderBy('name_en')->get(),
        ]);
    }

    /** Store slip + items */
    public function store(Request $request)
    {
        $payload = $request->all();
        if (isset($payload['items']) && is_array($payload['items'])) {
            foreach ($payload['items'] as $k => $row) {
                if (($row['product_id'] ?? null) === '__new__') {
                    $payload['items'][$k]['product_id'] = null;
                }
            }
        }

        $data = validator($payload, [
            'po_sn'         => ['required', 'string', 'max:50', 'unique:purchase_slips,po_sn'],
            'po_date'       => ['required', 'date'],
            'department_id' => ['required', 'exists:departments,id'],
            'remarks'       => ['nullable', 'string', 'max:2000'],

            'items'                        => ['required', 'array', 'min:1'],
            'items.*.product_id'           => ['nullable', 'exists:products,id', 'required_without:items.*.temp_name'],
            'items.*.temp_name'            => ['nullable', 'string', 'max:255', 'required_without:items.*.product_id'],
            'items.*.temp_sn'              => ['nullable', 'string', 'max:100'],
            'items.*.unit'                 => ['nullable', 'string', 'max:20'],
            'items.*.ordered_qty'          => ['required', 'numeric', 'gt:0'],
            'items.*.max_rate'             => ['required', 'numeric', 'gte:0'],
            'items.*.item_category_id'     => ['required', 'exists:item_categories,id'],
        ], [
            'items.*.product_id.required_without' => 'Pick from suggestions or type an item name.',
            'items.*.temp_name.required_without'  => 'Type an item name if not selecting a suggestion.',
        ])->validate();

        DB::transaction(function () use ($data) {
            $slip = PurchaseSlip::create([
                'po_sn'         => trim($data['po_sn']),
                'po_date'       => $data['po_date'],
                'department_id' => $data['department_id'],
                'remarks'       => $data['remarks'] ?? null,
            ]);

            foreach ($data['items'] as $row) {
                PurchaseSlipItem::create([
                    'purchase_slip_id' => $slip->id,
                    'product_id'       => $row['product_id'] ?? null,
                    'temp_name'        => $row['temp_name'] ?? null,
                    'temp_sn'          => $row['temp_sn'] ?? null,
                    'unit'             => $row['unit'] ?? null,
                    'item_category_id' => $row['item_category_id'] ?? null,
                    'ordered_qty'      => $row['ordered_qty'],
                    'max_rate'         => $row['max_rate'],
                    'line_total'       => (float)$row['ordered_qty'] * (float)$row['max_rate'],
                ]);
            }
        });

        return redirect()->route('slips.index')->with('success', 'Purchase slip created.');
    }

    public function show(PurchaseSlip $slip)
    {
        $slip->load(['department', 'items.itemCategory']);

        // If you need purchased qty by product from purchases linked to this slip:
        $purchasedByProduct = PurchaseSlip::query()
            ->selectRaw(' SUM(qty) as qty')
            ->whereHas('purchase', fn($p) => $p->where('purchase_slip_id', $slip->id))
        
            ->pluck('qty');

        return view('Backend.slips.show', [
            'slip' => $slip,
            'purchasedByProduct' => $purchasedByProduct,
        ]);
    }

    public function edit(PurchaseSlip $slip)
    {
        $slip->load('items');

        // Prepare items for JavaScript
        $slipItemsForJs = $slip->items->map(function ($item) {
            return [
                'product_id'        => $item->product_id,
                'temp_name'         => $item->temp_name,
                'temp_sn'           => $item->temp_sn,
                'ordered_qty'       => $item->ordered_qty,
                'max_rate'          => $item->max_rate,
                'unit'              => $item->unit,
                'item_category_id'  => $item->item_category_id,
            ];
        })->toArray();

        return view('Backend.slips.edit', [
            'slip'           => $slip,
            'slipItemsForJs' => $slipItemsForJs,
            'departments'    => Department::orderBy('name')->get(),
            'item_category'  => ItemCategory::orderBy('name_en')->get(),
        ]);
    }

    public function update(Request $request, PurchaseSlip $slip)
    {
        $payload = $request->all();
        if (isset($payload['items']) && is_array($payload['items'])) {
            foreach ($payload['items'] as $k => $row) {
                if (($row['product_id'] ?? null) === '__new__') {
                    $payload['items'][$k]['product_id'] = null;
                }
            }
        }

        $data = validator($payload, [
            'po_sn'         => ['required', 'string', 'max:50', Rule::unique('purchase_slips', 'po_sn')->ignore($slip->id)],
            'po_date'       => ['required', 'date'],
            'department_id' => ['required', 'exists:departments,id'],
            'remarks'       => ['nullable', 'string', 'max:2000'],

            'items'                        => ['required', 'array', 'min:1'],
            'items.*.product_id'           => ['nullable', 'exists:products,id', 'required_without:items.*.temp_name'],
            'items.*.temp_name'            => ['nullable', 'string', 'max:255', 'required_without:items.*.product_id'],
            'items.*.temp_sn'              => ['nullable', 'string', 'max:100'],
            'items.*.unit'                 => ['nullable', 'string', 'max:20'],
            'items.*.ordered_qty'          => ['required', 'numeric', 'gt:0'],
            'items.*.max_rate'             => ['required', 'numeric', 'gte:0'],
            'items.*.item_category_id'     => ['required', 'exists:item_categories,id'],
        ], [
            'items.*.product_id.required_without' => 'Pick from suggestions or type an item name.',
            'items.*.temp_name.required_without'  => 'Type an item name if not selecting a suggestion.',
        ])->validate();

        DB::transaction(function () use ($slip, $data) {
            $slip->update([
                'po_sn'         => $data['po_sn'],
                'po_date'       => $data['po_date'],
                'department_id' => $data['department_id'],
                'remarks'       => $data['remarks'] ?? null,
            ]);

            // If you want to block reducing qty below already purchased qty for same product_id:
            $purchasedByProduct = PurchaseSlip::query()
                ->selectRaw('product_id, SUM(qty) as qty')
                ->whereHas('purchase', fn($p) => $p->where('purchase_slip_id', $slip->id))
       ;

            // Replace items
            $slip->items()->delete();

            foreach ($data['items'] as $row) {
                $productId = $row['product_id'] ?? null;
                if ($productId) {
                    $alreadyPurchased = (float)($purchasedByProduct[$productId] ?? 0);
                    if ((float)$row['ordered_qty'] + 1e-9 < $alreadyPurchased) {
                        abort(422, "Ordered qty cannot be less than already purchased ({$alreadyPurchased}).");
                    }
                }

                PurchaseSlipItem::create([
                    'purchase_slip_id' => $slip->id,
                    'product_id'       => $productId,
                    'temp_name'        => $row['temp_name'] ?? null,
                    'temp_sn'          => $row['temp_sn'] ?? null,
                    'unit'             => $row['unit'] ?? null,
                    'item_category_id' => $row['item_category_id'] ?? null,
                    'ordered_qty'      => $row['ordered_qty'],
                    'max_rate'         => $row['max_rate'],
                    'line_total'       => (float)$row['ordered_qty'] * (float)$row['max_rate'],
                ]);
            }
        });

        return redirect()->route('slips.index')->with('success', 'Purchase slip updated.');
    }

    public function destroy(PurchaseSlip $slip)
    {
        if ($slip->purchases()->exists()) {
            return back()->with('error', 'Cannot delete slip with existing purchases.');
        }
        $slip->delete();
        return back()->with('success', 'Purchase slip deleted.');
    }

    public function print(PurchaseSlip $slip)
    {
        $slip->load(['department', 'items.itemCategory']);

        $items = $slip->items->map(function ($it, $idx) {
            $desc   = $it->temp_name ?: ('Product #' . $it->product_id);
            $unit   = $it->unit ?: '';
            $qty    = (float)$it->ordered_qty;
            $rate   = (float)$it->max_rate;
            $total  = $it->line_total ?: $qty * $rate;
            $cat = $it->itemCategory?->name_en
                ? ( ' (' . mb_substr($it->itemCategory->name_np ?? '', 0, 2) . ')')
                : '';


            return [
                'sn'        => $idx + 1,
                'desc'      => $desc,
                'category'  => $cat,
                'unit'      => $unit,
                'min_stock' => '',
                'store_bal' => '',
                'required'  => number_format($qty, 3),
                'amount'    => number_format($total, 2),
                'budget'    => $cat,
                'remark'    => '',
            ];
        });

        return view('Backend.slips.print', [
            'slip'  => $slip,
            'items' => $items,
        ]);
    }
}
