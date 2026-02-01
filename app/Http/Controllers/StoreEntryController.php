<?php
// app/Http/Controllers/StoreEntryController.php
namespace App\Http\Controllers;

use App\Models\{
    StoreEntry,
    StoreEntryItem,
    Purchase,
    PurchaseItem,
    ItemCategory,
    Category,
    Brand,
    StoreOut,
    StoreOutItem,
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StoreEntryController extends Controller
{
    public function index(Request $request)
    {
        $q = StoreEntry::with(['purchase', 'supplier'])->latest('id');

        if ($s = trim((string)$request->get('search'))) {
            $q->where(function ($w) use ($s) {
                $w->where('purchase_sn', 'like', "%{$s}%")
                    ->orWhere('supplier_name', 'like', "%{$s}%");
            });
        }

        $entries = $q->paginate(15)->appends($request->only('search'));
        return view('Backend.store.index', compact('entries'));
    }

    /** Step 1: Let user pick Item Category / Product Category / Brand per line */
    public function prepare(Purchase $purchase)
    {
        $purchase->load(['supplier', 'items.product']);
        $itemCategories = ItemCategory::orderBy('name_en')->get(['id', 'name_en']);
        $productCategories = Category::orderBy('name')->get(['id', 'name']);
        $brands = Brand::orderBy('name')->get(['id', 'name']);

        // Build rows with smart defaults (from line, else product)
        $rows = $purchase->items->map(function ($line) {
            $name = $line->temp_name ?: ($line->product->name ?? '—');
            $sn   = $line->temp_sn   ?: ($line->product->sku ?? null);
            return [
                'purchase_item_id' => $line->id,
                'display_name'     => $name,
                'sn'               => $sn,
                'unit'             => $line->unit ?: ($line->product->unit ?? null),
                'qty'              => (float)$line->qty,
                'rate'             => (float)$line->rate,
                'total'            => round((float)$line->qty * (float)$line->rate, 2),

                // defaults
                'item_category_id' => $line->item_category_id
                    ?? ($line->product->item_category_id ?? null),
                'category_id'      => $line->product->category_id ?? null,
                'brand_id'         => $line->product->brand_id ?? null,
            ];
        });

        return view('Backend.store.prepare', compact(
            'purchase',
            'rows',
            'itemCategories',
            'productCategories',
            'brands'
        ));
    }

    /** Step 2: Create/refresh Store Entry using the user's selections */
    public function postFromPurchase(Request $request, Purchase $purchase)
    {
        $data = $request->validate([
            'mapping'                               => ['required', 'array', 'min:1'],
            'mapping.*.purchase_item_id'            => ['required', 'exists:purchase_items,id'],
            'mapping.*.item_category_id'            => ['nullable', 'exists:item_categories,id'],
            'mapping.*.category_id'                 => ['nullable', 'exists:categories,id'],
            'mapping.*.brand_id'                    => ['nullable', 'exists:brands,id'],
        ]);

        $purchase->load(['supplier', 'items.product']);
        $mapById = collect($data['mapping'])->keyBy('purchase_item_id');

        return DB::transaction(function () use ($purchase, $mapById) {

            // 1️⃣ Create or update Store Entry header
            $entry = StoreEntry::updateOrCreate(
                ['purchase_id' => $purchase->id],
                [
                    'supplier_id'   => $purchase->supplier_id,
                    'purchase_sn'   => $purchase->purchase_sn,
                    'purchase_date' => (string)$purchase->purchase_date,
                    'supplier_name' => $purchase->supplier->name ?? null,
                    'remarks'       => $purchase->remarks,
                ]
            );

            // 2️⃣ Rebuild all store_entry_items
            $entry->items()->delete();

            $bulk = [];
            $storeDate = now()->toDateString();

            foreach ($purchase->items as $line) {
                $choice = $mapById->get($line->id) ?? [];

                $name = $line->temp_name ?: ($line->product->name ?? '—');
                $sn   = $line->temp_sn   ?: ($line->product->sku ?? null);
                $unit = $line->unit      ?: ($line->product->unit ?? null);

                $qty  = (float)$line->qty;
                $rate = (float)$line->rate;

                $rateExVat = $rate;
                if (($purchase->tax_mode ?? 'PAN') === 'VAT') {
                    $vp = (float)($purchase->vat_percent ?? 0);
                    if ($vp > 0) {
                        $rateExVat = $rate * (1 + ($vp / 100));
                    }
                }

                $rateExVat = round($rateExVat, 4);


                // Selected categories/brand
                $itemCategoryId = $choice['item_category_id']
                    ?? $line->item_category_id
                    ?? ($line->product->item_category_id ?? null);

                $categoryId = $choice['category_id']
                    ?? ($line->product->category_id ?? null);

                $brandId = $choice['brand_id']
                    ?? ($line->product->brand_id ?? null);

                // 3️⃣ Build Store Entry Item
                $bulk[] = new StoreEntryItem([
                    'purchase_item_id' => $line->id,
                    'product_id'       => $line->product_id,
                    'item_category_id' => $itemCategoryId,
                    'category_id'      => $categoryId,
                    'brand_id'         => $brandId,
                    'item_name'        => $name,
                    'item_sn'          => $sn,
                    'unit'             => $unit,
                    'qty'              => $qty,
                    'rate' => $rateExVat,
                    'total_price' => round($qty * $rateExVat, 2),
                ]);

                // 4️⃣ Update purchase_items table
                //     store_entry_sn ← category_id
                //     store_entry_date ← current date
                $line->update([
                    'store_entry_sn'   => $categoryId,
                    'store_entry_date' => $storeDate,
                    'item_category_id' => $itemCategoryId, // keep consistent
                ]);
            }

            // 5️⃣ Save all items in one go
            if ($bulk) {
                $entry->items()->saveMany($bulk);
            }

            return redirect()
                ->route('store.show', $entry)
                ->with('success', 'Store entry created and purchase items updated with store entry data.');
        });
    }

    // app/Http/Controllers/StoreEntryController.php

    public function show(StoreEntry $storeEntry)
    {
        $storeEntry->load([
            'purchase.supplier',
            'purchase.slip',
            'items' => function ($q) {
                $q->with(['categoryRef', 'product', 'purchaseItem']);
            },
        ]);

        // Header/meta used by the view
        $meta = [
            'purchase_sn'   => $storeEntry->purchase_sn,
            'purchase_date' => $storeEntry->purchase_date,
            'supplier'      => $storeEntry->supplier_name ?? $storeEntry->purchase?->supplier?->name ?? '—',
            'slip_sn'       => $storeEntry->purchase?->slip?->po_sn,
            'slip_date'     => optional($storeEntry->purchase?->slip?->po_date)->format('Y-m-d'),
            'remarks'       => $storeEntry->purchase?->remarks ?? '',
            'id'            => $storeEntry->id,
        ];

        // Rows for table
        $rows = $storeEntry->items->map(function ($it, $i) {
            $amt = (float) $it->total_price;
            return [
                'sn'         => $i + 1,
                'name'       => $it->item_name,
                'sn_code'    => $it->item_sn,
                'unit'       => $it->unit ?: '—',
                'qty'        => number_format((float)$it->qty, 1),
                'rate'       => number_format((float)$it->rate, 2),
                'amount'     => number_format($amt, 2),
                'category'   => $it->categoryRef?->name ?? '—',   // Product Category
                'category_id' => $it->category_id,
                'ledger'     => $it->purchaseItem?->store_entry_sn ?? '', // you stored category_id here
            ];
        });

        return view('Backend.store.show', compact('storeEntry', 'meta', 'rows'));
    }



    public function ledgerByCategory(Request $request, int $categoryId)
    {
        $from = $request->query('from');
        $to   = $request->query('to');

        $category = Category::findOrFail($categoryId);

        // ✅ Load everything view needs (safe + complete)
        $items = StoreEntryItem::query()
            ->where('category_id', $categoryId)
            ->with([
                'entry.purchase.slip',
                'entry.purchase.supplier',
                'product',
                'storeOutItems.storeOut.department',
                // if employee relation exists it will load; if not, it won't be used (we use safe fallback)
                'storeOutItems.storeOut.employee',
            ])
            ->when($from, function ($q) use ($from) {
                $q->whereHas('entry.purchase', fn($p) => $p->whereDate('purchase_date', '>=', $from));
            })
            ->when($to, function ($q) use ($to) {
                $q->whereHas('entry.purchase', fn($p) => $p->whereDate('purchase_date', '<=', $to));
            })
            ->orderBy('id')
            ->get();

        // Totals
        $grandInQty     = 0.0;
        $grandInAmount  = 0.0;

        // IMPORTANT: grandOut/grandBaki should reflect ONLY consumable movement
        $grandOutQty    = 0.0;
        $grandOutAmount = 0.0;

        $rows = $items->map(function ($it) use (&$grandInQty, &$grandInAmount, &$grandOutQty, &$grandOutAmount) {

            $purchase = $it->entry?->purchase;
            $slip     = $purchase?->slip;

            $inQty    = (float) ($it->qty ?? 0);
            $rate     = (float) ($it->rate ?? 0);
            $inAmount = (float) ($it->total_price ?? ($inQty * $rate));

            // ✅ category type detect (your type is 0/1 in DB)
            $cat = $it->itemCategory;
            $isConsumable    = $cat?->isConsumable() ?? true;
            $isNonConsumable = $cat?->isNonConsumable() ?? false;

            // ✅ only active out (exclude returned)
            $outItems = $it->storeOutItems->whereNull('returned_at');

            $outQty    = (float) $outItems->sum('qty');
            $outAmount = $outQty * $rate;

            // ✅ ONLY consumable affects balance (BAKI + totals)
            $effectiveOutQty    = $isConsumable ? $outQty : 0.0;
            $effectiveOutAmount = $isConsumable ? $outAmount : 0.0;

            $bakiQty    = $inQty - $effectiveOutQty;
            $bakiAmount = $inAmount - $effectiveOutAmount;

            // ✅ pick latest StoreOut for expense note/date/department/employee/remarks
            $latestOutItem = $outItems->sortByDesc('id')->first();
            $so = $latestOutItem?->storeOut;

            $expenseSn   = $so?->store_out_sn ?? $so?->out_sn ?? ($so ? ('OUT-' . $so->id) : '—');
            $expenseDate = $so?->store_out_date_bs
                ?? ($so?->out_date_bs ?? ($so?->out_date ? date('Y-m-d', strtotime($so->out_date)) : ($so?->created_at?->format('Y-m-d') ?? '—')));

            $destination = $so?->department?->name ?? '—';

            $employeeName = $so?->employee?->full_name
                ?? $so?->employee?->name
                ?? ($so?->employee_name ?? $so?->issued_by ?? '—');

            $employeeDept = $so?->employee?->department?->name ?? '';

            // ✅ remarks: show store-out remarks + issued-to
            $remarkParts = [];
            if (!empty($so?->remarks)) $remarkParts[] = $so->remarks;
            if ($employeeName !== '—') {
                $remarkParts[] = 'Issued To: ' . $employeeName . ($employeeDept ? " ($employeeDept)" : '');
            }
            $finalRemarks = implode(' | ', $remarkParts);

            // Accumulate totals
            $grandInQty    += $inQty;
            $grandInAmount += $inAmount;

            // IMPORTANT: totals reflect ONLY consumable movement
            $grandOutQty    += $effectiveOutQty;
            $grandOutAmount += $effectiveOutAmount;



            return [
                // खरिद आदेश (PO Slip)

                'slip_sn'   => ($it->purchaseItem?->slipItem?->slip)->po_sn ?? '',
                'slip_date' => optional(
                    $it->purchaseItem?->slipItem?->slip?->po_date
                )->format('Y-m-d') ?? '—',

                // suppliers + (product name)
                'supplier'  => $purchase?->supplier?->name ?? ($it->entry?->supplier_name ?? '—'),
                'desc'      => $it->product?->name ?? $it->item_name ?? '—',

                // store receipt (purchase/store entry)
                'purchase_sn'   => $purchase?->purchase_sn ?? ($it->entry?->purchase_sn ?? '—'),
                'purchase_date' => $purchase?->purchase_date
                    ? date('Y-m-d', strtotime($purchase->purchase_date))
                    : ($it->entry?->purchase_date ?? '—'),

                // IN
                'qty'    => number_format($inQty, 3),
                'unit'   => $it->unit ?? '',
                'rate'   => number_format($rate, 2),
                'amount' => number_format($inAmount, 2),

                // ✅ KHARCHA (expense) - show actual issue (even for non-consumable)
                'expense_sn'   => $expenseSn,
                'expense_date' => $expenseDate,
                'destination'  => $destination,
                'out_qty'      => number_format($outQty, 3),
                'out_amount'   => number_format($outAmount, 2),

                // ✅ BAKI - only consumable reduces baki
                'baki_qty'     => number_format($bakiQty, 3),
                'baki_amount'  => number_format($bakiAmount, 2),

                // ✅ remarks/cafiyat
                'remarks' => $finalRemarks,
            ];
        })->values()->all();

        $grandBakiQty    = $grandInQty - $grandOutQty;
        $grandBakiAmount = $grandInAmount - $grandOutAmount;

        $meta = [
            'category_id'   => (int) $category->id,
            'category_name' => $category->name ?? ('Category #' . $category->id),
            'from'          => $from,
            'to'            => $to,

            'grand_in'      => number_format($grandInQty, 3),
            'grand_out'     => number_format($grandOutQty, 3),      // ✅ only consumable
            'grand_baki'    => number_format($grandBakiQty, 3),     // ✅ only consumable

            // for your bottom "जम्मा" row (income amount total)
            'grand_total'   => number_format($grandInAmount, 2),

            // optional
            'grand_in_amount'   => number_format($grandInAmount, 2),
            'grand_out_amount'  => number_format($grandOutAmount, 2),    // ✅ only consumable
            'grand_baki_amount' => number_format($grandBakiAmount, 2),   // ✅ only consumable
        ];


        return view('Backend.store.ledger_category', compact('rows', 'meta'));
    }

    public function ledger(Request $request)
    {
        // Optional date filters (apply to purchase_date)
        $from = $request->date('from');
        $to   = $request->date('to');

        $q = StoreEntryItem::query()
            ->selectRaw('category_id, COUNT(*) as items_count, SUM(total_price) as total_amount')
            ->with(['category']) // needs relation on StoreEntryItem
            ->whereNotNull('category_id')
            ->when(
                $from,
                fn($x) =>
                $x->whereHas('entry.purchase', fn($p) => $p->whereDate('purchase_date', '>=', $from))
            )
            ->when(
                $to,
                fn($x) =>
                $x->whereHas('entry.purchase', fn($p) => $p->whereDate('purchase_date', '<=', $to))
            )
            ->groupBy('category_id')
            ->orderBy('category_id');

        $groups = $q->get();

        // Decorate for view
        $rows = $groups->map(function ($g) {
            $name = optional($g->category)->name ?? ('Category #' . $g->category_id);
            return [
                'category_id'   => (int)$g->category_id,
                'category_name' => $name,
                'items_count'   => (int)$g->items_count,
                'total_amount'  => number_format((float)$g->total_amount, 2),
            ];
        });

        return view('Backend.store.categories', [
            'rows' => $rows,
            'filters' => [
                'from' => $from ? $from->format('Y-m-d') : null,
                'to'   => $to   ? $to->format('Y-m-d')   : null,
            ],
        ]);
    }



    // public function categoryItems(int $categoryId, Request $request)
    // {
    //     $search = trim((string)$request->get('search', ''));
    //     $from   = $request->date('from');
    //     $to     = $request->date('to');

    //     $q = \App\Models\StoreEntryItem::query()
    //         ->with([
    //             'category:id,name',
    //             'entry:id,purchase_id',
    //             'entry.purchase:id,purchase_sn,purchase_date,supplier_id,purchase_slip_id,remarks',
    //             'entry.purchase.supplier:id,name',
    //             'entry.purchase.slip:id,po_sn,po_date',
    //         ])
    //         ->where('category_id', $categoryId);

    //     if ($search !== '') {
    //         $q->where(function($w) use ($search) {
    //             $w->where('item_name', 'like', "%{$search}%")
    //               ->orWhere('item_sn', 'like', "%{$search}%")
    //               ->orWhereHas('entry.purchase.supplier', fn($s) => $s->where('name', 'like', "%{$search}%"))
    //               ->orWhereHas('entry.purchase', fn($p) => $p->where('purchase_sn', 'like', "%{$search}%"))
    //               ->orWhereHas('entry.purchase.slip', fn($sl) => $sl->where('po_sn', 'like', "%{$search}%"));
    //         });
    //     }

    //     if ($from) {
    //         $q->whereHas('entry.purchase', fn($p) => $p->whereDate('purchase_date', '>=', $from));
    //     }
    //     if ($to) {
    //         $q->whereHas('entry.purchase', fn($p) => $p->whereDate('purchase_date', '<=', $to));
    //     }

    //     $items = $q->orderBy('id', 'desc')->paginate(20)->appends($request->only('search','from','to'));

    //     $categoryName = \App\Models\Category::find($categoryId)?->name ?? "Category #{$categoryId}";

    //     return view('Backend.store.category_items', [
    //         'categoryId'   => $categoryId,
    //         'categoryName' => $categoryName,
    //         'items'        => $items,
    //         'filters'      => [
    //             'search' => $search,
    //             'from'   => $from ? $from->format('Y-m-d') : null,
    //             'to'     => $to   ? $to->format('Y-m-d')   : null,
    //         ],
    //     ]);
    // }

    public function browseRoot()
    {
        return view('Backend.store.browse_root');
    }

    /** List only Item Categories that have Store Entry Items */
    public function browseItemCategories()
    {
        $ics = StoreEntryItem::query()
            ->selectRaw('item_category_id, COUNT(*) as items_count, SUM(total_price) as total_amount')
            ->whereNotNull('item_category_id')
            ->groupBy('item_category_id')
            ->orderBy('item_category_id')
            ->get()
            ->map(function ($row) {
                $ic = ItemCategory::find($row->item_category_id);
                return [
                    'item_category_id' => $row->item_category_id,
                    'name'             => $ic?->name_en ?? ("Item Category #" . $row->item_category_id),
                    'items_count'      => (int)$row->items_count,
                    'total_amount'     => number_format((float)$row->total_amount, 2),
                ];
            });

        return view('Backend.store.browse_item_categories', compact('ics'));
    }

    /** From a chosen Item Category, show only Product Categories that exist under it */
    public function browseProductCategoriesUnderIC(int $itemCategoryId)
    {
        $ic = ItemCategory::find($itemCategoryId);

        // Aggregate product categories within this item category
        $rows = StoreEntryItem::query()
            ->where('item_category_id', $itemCategoryId)
            ->whereNotNull('category_id')
            ->selectRaw('category_id, COUNT(*) as items_count, SUM(total_price) as total_amount')
            ->groupBy('category_id')
            ->orderBy('category_id')
            ->get()
            ->map(function ($row) {
                $cat = Category::find($row->category_id);
                return [
                    'category_id'  => $row->category_id,
                    'category'     => $cat?->name ?? ("Category #" . $row->category_id),
                    'items_count'  => (int)$row->items_count,
                    'total_amount' => number_format((float)$row->total_amount, 2),
                ];
            });

        return view('Backend.store.browse_ic_categories', [
            'itemCategoryId'   => $itemCategoryId,
            'itemCategoryName' => $ic?->name_en ?? ("Item Category #" . $itemCategoryId),
            'rows'             => $rows,
        ]);
    }

    /**
     * EXISTING: List StoreEntryItems for a product category
     * UPDATE: accept optional item_category filter via ?ic=ID
     */
    public function categoryItems(int $categoryId, Request $request)
    {
        $search = trim((string)$request->get('search', ''));
        $from   = $request->date('from');
        $to     = $request->date('to');
        $icId   = $request->integer('ic') ?: null;   // NEW optional filter

        $q = StoreEntryItem::query()
            ->with([
                'category:id,name',
                'entry:id,purchase_id',
                'entry.purchase:id,purchase_sn,purchase_date,supplier_id,purchase_slip_id,remarks',
                'entry.purchase.supplier:id,name',
                'entry.purchase.slip:id,po_sn,po_date',
            ])
            ->where('category_id', $categoryId);

        if ($icId) {
            $q->where('item_category_id', $icId);
        }

        if ($search !== '') {
            $q->where(function ($w) use ($search) {
                $w->where('item_name', 'like', "%{$search}%")
                    ->orWhere('item_sn', 'like', "%{$search}%")
                    ->orWhereHas('entry.purchase.supplier', fn($s) => $s->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('entry.purchase', fn($p) => $p->where('purchase_sn', 'like', "%{$search}%"))
                    ->orWhereHas('entry.purchase.slip', fn($sl) => $sl->where('po_sn', 'like', "%{$search}%"));
            });
        }

        if ($from) {
            $q->whereHas('entry.purchase', fn($p) => $p->whereDate('purchase_date', '>=', $from));
        }
        if ($to) {
            $q->whereHas('entry.purchase', fn($p) => $p->whereDate('purchase_date', '<=', $to));
        }

        $items = $q->orderBy('id', 'desc')->paginate(20)->appends($request->only('search', 'from', 'to', 'ic'));

        $categoryName = Category::find($categoryId)?->name ?? "Category #{$categoryId}";
        $icName = $icId ? (ItemCategory::find($icId)?->name_en ?? "Item Category #{$icId}") : null;

        return view('Backend.store.category_items', [
            'categoryId'   => $categoryId,
            'categoryName' => $categoryName,
            'items'        => $items,
            'filters'      => [
                'search' => $search,
                'from'   => $from ? $from->format('Y-m-d') : null,
                'to'     => $to   ? $to->format('Y-m-d')   : null,
                'ic'     => $icId,
                'icName' => $icName,
            ],
        ]);
    }
}
