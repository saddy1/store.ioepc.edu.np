<?php

namespace App\Http\Controllers;

use App\Models\StoreOut;
use App\Models\StoreOutItem;
use App\Models\StoreEntryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StoreOutController extends Controller
{
    public function searchEntryItems(Request $request)
    {
        $term = trim((string) $request->get('q', ''));

        $items = StoreEntryItem::with('itemCategory')
            ->when($term, function ($q) use ($term) {
                $q->where(function ($w) use ($term) {
                    $w->where('item_name', 'like', "%{$term}%")
                        ->orWhere('item_sn', 'like', "%{$term}%");
                });
            })
            ->orderBy('item_name')
            ->limit(20)
            ->get();

        return $items->map(function ($it) {
            $cat = $it->itemCategory;

            $isNonConsumable = $cat?->isNonConsumable() ?? false;

            $issued = (float) $it->storeOutItems()
                ->whereNull('returned_at')
                ->sum('qty');

            $total = (float) ($it->qty ?? 0);

            $available = max(0, $total - $issued);

            return [
                'id'                 => $it->id,
                'text'               => $it->item_name . ($it->item_sn ? ' [' . $it->item_sn . ']' : ''),
                'item_name'          => $it->item_name,
                'item_sn'            => $it->item_sn,
                'unit'               => $it->unit,
                'item_category_id'   => $it->item_category_id,
                'item_category_name' => $it->itemCategory?->name_en,

                'type'               => $cat?->typeLabel() ?? 'Consumable',
                'is_non_consumable'  => $isNonConsumable,

                'total_qty'          => $total,
                'issued_qty'         => $issued,
                'available_qty'      => $available,
            ];
        });
    }

    public function index(Request $request)
    {
        $q = StoreOut::query()
            ->with(['employee', 'department', 'items'])
            ->latest('store_out_date_bs')
            ->latest();

        if ($s = trim((string) $request->get('search', ''))) {
            $q->where(function ($w) use ($s) {
                $w->where('store_out_sn', 'like', "%{$s}%")
                    ->orWhere('store_out_date_bs', 'like', "%{$s}%")
                    ->orWhereHas('employee', function ($e) use ($s) {
                        $e->where('full_name', 'like', "%{$s}%")
                            ->orWhere('email', 'like', "%{$s}%")
                            ->orWhere('atten_no', 'like', "%{$s}%");
                    });
            });
        }

        $outs = $q->paginate(15)->appends($request->only('search'));
        return view('Backend.store_out.index', compact('outs'));
    }

    public function create(Request $request)
    {
        $itemId = $request->query('item_id');
        $prefillItem = null;

        if ($itemId) {
            $prefillItem = StoreEntryItem::with(['itemCategory'])->find($itemId);
        }

        return view('Backend.store_out.create', compact('prefillItem'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'store_out_sn'      => ['required', 'string', 'max:50', 'unique:store_outs,store_out_sn'],
            'store_out_date_bs' => ['required', 'string', 'max:20'],
            'department_id'     => ['required', 'exists:departments,id'],
            'employee_id'       => ['required', 'exists:employees,id'],
            'remarks'           => ['nullable', 'string', 'max:1000'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.store_entry_item_id' => ['required', 'exists:store_entry_items,id'],
            'items.*.qty' => ['required', 'numeric', 'min:0.001'],
            'items.*.remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        // prevent duplicates in same request
        $ids = array_map(fn ($x) => (int) $x['store_entry_item_id'], $data['items']);
        if (count($ids) !== count(array_unique($ids))) {
            throw ValidationException::withMessages([
                'items' => 'Same item selected multiple times. Please keep each item only once.',
            ]);
        }

        return DB::transaction(function () use ($data) {

            $storeOut = StoreOut::create([
                'store_out_sn'      => $data['store_out_sn'],
                'store_out_date_bs' => $data['store_out_date_bs'],
                'department_id'     => $data['department_id'],
                'employee_id'       => $data['employee_id'],
                'remarks'           => $data['remarks'] ?? null,
            ]);

            foreach ($data['items'] as $i => $row) {
                $entryItem = StoreEntryItem::with('itemCategory')->findOrFail($row['store_entry_item_id']);
                $issueQty  = (float) $row['qty'];

                $cat = $entryItem->itemCategory;

                $isConsumable    = $cat?->isConsumable() ?? true;
                $isNonConsumable = $cat?->isNonConsumable() ?? false;

                // ✅ One unified availability rule: total - active issued (not returned)
                $available = $this->availableQtyForAssignment($entryItem);

                // ✅ Consumable: must have enough stock
                if ($isConsumable) {
                    if ($issueQty > $available + 0.000001) {
                        throw ValidationException::withMessages([
                            "items.$i.qty" => "Not enough stock. Available: " . number_format($available, 3),
                        ]);
                    }
                }

                // ✅ Non-consumable: cannot issue if already assigned (without return)
                // (same math, but clearer message)
                if ($isNonConsumable) {
                    if ($issueQty > $available + 0.000001) {
                        throw ValidationException::withMessages([
                            "items.$i.qty" => "This non-consumable item is already assigned. Return it first. Available: " . number_format($available, 3),
                        ]);
                    }
                }

                $rate = (float) ($entryItem->rate ?? 0);

                StoreOutItem::create([
                    'store_out_id'        => $storeOut->id,
                    'store_entry_item_id' => $entryItem->id,

                    // required columns
                    'item_category_id'    => $entryItem->item_category_id,
                    'category_id'         => $entryItem->category_id,
                    'brand_id'            => $entryItem->brand_id,

                    'item_name'           => $entryItem->item_name,
                    'item_sn'             => $entryItem->item_sn,
                    'unit'                => $entryItem->unit,

                    'qty'                 => $issueQty,
                    'rate'                => $rate,
                    'total_price'         => $issueQty * $rate,

                    'remarks'             => $row['remarks'] ?? null,
                ]);
            }

            return redirect()
                ->route('store.out.show', $storeOut)
                ->with('success', 'Store Out created.');
        });
    }

    public function markReturned(StoreOutItem $storeOutItem)
    {
        $entryItem = $storeOutItem->storeEntryItem()->with('itemCategory')->firstOrFail();
        $cat = $entryItem->itemCategory;

        if (!($cat?->isNonConsumable() ?? false)) {
            abort(400, 'Return is only applicable for non-consumable items.');
        }

        if ($storeOutItem->returned_at) {
            return back()->with('info', 'Item is already marked as returned.');
        }

        $storeOutItem->update(['returned_at' => now()]);
        return back()->with('success', 'Item marked as returned.');
    }

    public function show(StoreOut $storeOut)
    {
        $storeOut->load(['employee', 'department', 'items.storeEntryItem.itemCategory']);
        return view('Backend.store_out.show', compact('storeOut'));
    }

    public function print(StoreOut $storeOut)
    {
        $storeOut->load(['employee', 'department', 'items.storeEntryItem.itemCategory']);
        return view('Backend.store_out.print', compact('storeOut'));
    }

    /**
     * ✅ Single source of truth for "available qty"
     * available = entry.qty - active_issued(where returned_at is null)
     */
    private function availableQtyForAssignment(StoreEntryItem $entryItem): float
    {
        $issued = (float) $entryItem->storeOutItems()
            ->whereNull('returned_at')
            ->sum('qty');

        $total = (float) ($entryItem->qty ?? 0);

        return max(0, $total - $issued);
    }

    /**
     * ✅ Backward compatible alias (so even if you call availableQty() anywhere,
     * it will NEVER crash again).
     */
    private function availableQty(StoreEntryItem $entryItem): float
    {
        return $this->availableQtyForAssignment($entryItem);
    }
}
