<?php

namespace App\Http\Controllers;

use App\Models\{
    StoreOut,
    StoreOutItem,
    StoreEntryItem
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StoreOutController extends Controller
{
public function searchEntryItems(Request $request)
{
    $term = trim((string)$request->get('q', ''));

    $items = StoreEntryItem::with('itemCategory')
        ->when($term, function ($q) use ($term) {
            $q->where('item_name', 'like', "%{$term}%")
              ->orWhere('item_sn', 'like', "%{$term}%");
        })
        ->orderBy('item_name')
        ->limit(20)
        ->get();

    return $items->map(function ($it) {
        return [
            'id'                  => $it->id,
            'text'                => $it->item_name . ($it->item_sn ? ' ['.$it->item_sn.']' : ''),
            'item_name'           => $it->item_name,
            'item_sn'             => $it->item_sn,
            'unit'                => $it->unit,
            'item_category_id'    => $it->item_category_id,
            'item_category_name'  => $it->itemCategory?->name_en,
        ];
    });
}

    public function index(Request $request)
    {
        $q = StoreOut::query()
            ->with(['employee','items'])
            ->latest('store_out_date_bs')
            ->latest();

        if ($s = trim((string)$request->get('search',''))) {
            $q->where('store_out_sn','like',"%{$s}%")
              ->orWhere('store_out_date_bs','like',"%{$s}%")
              ->orWhereHas('employee', function ($w) use ($s) {
                  $w->where('full_name','like',"%{$s}%")
                    ->orWhere('email','like',"%{$s}%")
                    ->orWhere('atten_no','like',"%{$s}%");
              });
        }

        $outs = $q->paginate(15)->appends($request->only('search'));

        return view('backend.store_out.index', compact('outs'));
    }

    // Create: optionally preselect a store_entry_item via ?item_id=
    public function create(Request $request)
    {
        $itemId = $request->query('item_id');
        $prefillItem = null;

        if ($itemId) {
            $prefillItem = StoreEntryItem::with(['entry','itemCategory'])->find($itemId);
        }

        return view('backend.store_out.create', [
            'prefillItem' => $prefillItem,
        ]);
    }

public function store(Request $request)
{
    $data = $request->validate([
        'store_out_sn'      => ['required','string','max:50','unique:store_outs,store_out_sn'],
        'store_out_date_bs' => ['required','string','max:10'],

        // either employee OR department (we’ll check in code)
        'employee_id'       => ['nullable','exists:employees,id'],
        'department_id'     => ['nullable','exists:departments,id'],

        'remarks'           => ['nullable','string','max:2000'],

        'items'                             => ['required','array','min:1'],
        'items.*.store_entry_item_id'       => ['required','exists:store_entry_items,id'],
        'items.*.qty'                       => ['required','numeric','gt:0'],
    ]);

    // Ensure exactly one of employee_id or department_id is provided:
    if (empty($data['employee_id']) && empty($data['department_id'])) {
        throw \Illuminate\Validation\ValidationException::withMessages([
            'employee_id' => ['Select either an employee or a department.'],
            'department_id' => ['Select either an employee or a department.'],
        ]);
    }
    if (!empty($data['employee_id']) && !empty($data['department_id'])) {
        throw \Illuminate\Validation\ValidationException::withMessages([
            'employee_id' => ['You cannot select both employee and department.'],
            'department_id' => ['You cannot select both employee and department.'],
        ]);
    }

    DB::transaction(function () use ($data) {

        $storeOut = StoreOut::create([
            'employee_id'       => $data['employee_id'] ?? null,
            'department_id'     => $data['department_id'] ?? null,
            'store_entry_id'    => null,
            'store_out_sn'      => $data['store_out_sn'],
            'store_out_date_bs' => $data['store_out_date_bs'],
            'remarks'           => $data['remarks'] ?? null,
        ]);

        foreach ($data['items'] as $row) {
            /** @var \App\Models\StoreEntryItem $entryItem */
            $entryItem = StoreEntryItem::with('itemCategory')
                ->lockForUpdate()
                ->findOrFail($row['store_entry_item_id']);

            $qtyToIssue = (float) $row['qty'];

            if ($qtyToIssue <= 0) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'items' => ["Quantity must be greater than zero for item {$entryItem->item_name}."],
                ]);
            }

            $category    = $entryItem->itemCategory;
            $type        = $category?->type ?? 'Consumable';
            $isConsumable = strcasecmp((string)$type, 'Consumable') === 0;

            if ($isConsumable) {
                // ---------- CONSUMABLE: decrease stock, cannot exceed remaining ----------
                if ($qtyToIssue > $entryItem->qty) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'items' => [
                            "Not enough stock for item {$entryItem->item_name}. ".
                            "Available: {$entryItem->qty}, requested: {$qtyToIssue}."
                        ],
                    ]);
                }

                // Reduce stock
                $entryItem->qty = $entryItem->qty - $qtyToIssue;
                $entryItem->total_price = round($entryItem->qty * $entryItem->rate, 2);
                $entryItem->save();

            } else {
                // ---------- NON-CONSUMABLE: do NOT decrease stock, prevent double assignment ----------
                $alreadyAssigned = StoreOutItem::query()
                    ->where('store_entry_item_id', $entryItem->id)
                    ->whereNull('returned_at')   // active assignment
                    ->exists();

                if ($alreadyAssigned) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'items' => [
                            "Item {$entryItem->item_name} ({$entryItem->item_sn}) is already assigned. ".
                            "You must process Store RETURN before assigning again."
                        ],
                    ]);
                }

                // For non-consumable we usually expect qty 1; enforce it here if you want:
                if ($qtyToIssue != 1.0) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'items' => [
                            "Non-consumable item {$entryItem->item_name} can only be issued with quantity 1."
                        ],
                    ]);
                }
            }

            // Create StoreOutItem (common for both)
            $storeOut->items()->create([
                'store_entry_item_id' => $entryItem->id,
                'item_category_id'    => $entryItem->item_category_id,
                'category_id'         => $entryItem->category_id,
                'brand_id'            => $entryItem->brand_id,
                'item_name'           => $entryItem->item_name,
                'item_sn'             => $entryItem->item_sn,
                'unit'                => $entryItem->unit,
                'qty'                 => $qtyToIssue,
            ]);
        }
    });

    return redirect()->route('store.out.index')->with('success','Store OUT created.');
}
public function markReturned(StoreOutItem $storeOutItem)
{
    // Only for non-consumables
    $entryItem = $storeOutItem->entryItem()->with('itemCategory')->first();
    $category  = $entryItem->itemCategory;
    $type      = $category?->type ?? 'Consumable';

    if (strcasecmp($type, 'Consumable') === 0) {
        abort(400, 'Return is only applicable for non-consumable items.');
    }

    if ($storeOutItem->returned_at) {
        return back()->with('info', 'Item is already marked as returned.');
    }

    $storeOutItem->returned_at = now();
    $storeOutItem->save();

    return back()->with('success', 'Item marked as returned.');
}


    public function show(StoreOut $storeOut)
    {
        $storeOut->load(['employee','items']);
        return view('backend.store_out.show', compact('storeOut'));
    }
}
