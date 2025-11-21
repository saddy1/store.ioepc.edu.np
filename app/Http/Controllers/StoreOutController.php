<?php

namespace App\Http\Controllers;

use App\Models\{
    StoreOut,
    StoreOutItem,
    StoreEntry,
    StoreEntryItem,
    ItemCategory,
    Employee
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StoreOutController extends Controller
{
    // List all store outs (admin side)
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
            'employee_id'       => ['required','exists:employees,id'],
            'remarks'           => ['nullable','string','max:2000'],

            'items'             => ['required','array','min:1'],
            'items.*.store_entry_item_id' => ['nullable','exists:store_entry_items,id'],
            'items.*.item_name'  => ['required','string','max:255'],
            'items.*.item_sn'    => ['nullable','string','max:100'],
            'items.*.unit'       => ['nullable','string','max:20'],
            'items.*.qty'        => ['required','numeric','gt:0'],
            'items.*.item_category_id' => ['nullable','exists:item_categories,id'],
        ]);

        DB::transaction(function () use ($data) {
            $storeOut = StoreOut::create([
                'employee_id'      => $data['employee_id'],
                'store_entry_id'   => null, // you can fill later if you want
                'store_out_sn'     => $data['store_out_sn'],
                'store_out_date_bs'=> $data['store_out_date_bs'],
                'remarks'          => $data['remarks'] ?? null,
            ]);

            $bulk = [];
            foreach ($data['items'] as $row) {
                $bulk[] = new StoreOutItem([
                    'store_entry_item_id' => $row['store_entry_item_id'] ?? null,
                    'item_category_id'    => $row['item_category_id'] ?? null,
                    'category_id'         => null,
                    'brand_id'            => null,
                    'item_name'           => $row['item_name'],
                    'item_sn'             => $row['item_sn'] ?? null,
                    'unit'                => $row['unit'] ?? null,
                    'qty'                 => $row['qty'],
                ]);
            }

            $storeOut->items()->saveMany($bulk);
        });

        return redirect()->route('store.out.index')->with('success','Store OUT created.');
    }

    public function show(StoreOut $storeOut)
    {
        $storeOut->load(['employee','items']);
        return view('backend.store_out.show', compact('storeOut'));
    }
}
