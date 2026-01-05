<?php

namespace App\Http\Controllers;


use App\Models\Student;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\ItemCategory;
use App\Models\Category;
use App\Models\StoreOut;


class DashboardController extends Controller
{
    public function home()
    {
        return view('Frontend.index');
    }

    public function admin( Request $r)
    {
        $admin = Admin::find(session('admin_id'));

        // Optional date filter (uses store_entries.purchase_date if provided)
        $from = $r->get('from');   // YYYY-MM-DD
        $to   = $r->get('to');     // YYYY-MM-DD

        // -------------------------
        // Basic counts
        // -------------------------
        $counts = [
            'products'      => Product::count(),
            'suppliers'     => Supplier::count(),
            'itemCategories' => ItemCategory::count(),
            'categories'    => Category::count(),
        ];

        // -------------------------
        // Purchase (IN) totals: total, consumable, non-consumable
        // -------------------------
        $purchaseTotalsQ = DB::table('store_entry_items as sei')
            ->join('store_entries as se', 'se.id', '=', 'sei.store_entry_id')
            ->join('item_categories as ic', 'ic.id', '=', 'sei.item_category_id');

        if ($from && $to) {
            $purchaseTotalsQ->whereBetween('se.purchase_date', [$from, $to]);
        }

        $purchaseTotals = $purchaseTotalsQ->selectRaw("
            COALESCE(SUM(sei.total_price),0) as total_amount,
            COALESCE(SUM(CASE WHEN ic.type=0 THEN sei.total_price ELSE 0 END),0) as consumable_amount,
            COALESCE(SUM(CASE WHEN ic.type=1 THEN sei.total_price ELSE 0 END),0) as non_consumable_amount,
            COALESCE(SUM(sei.qty),0) as total_qty
        ")->first();

        // -------------------------
        // Stock remaining + stock value (Remaining = IN - OUT(active))
        // -------------------------
        $issuedSub = DB::table('store_out_items')
            ->selectRaw('store_entry_item_id, SUM(qty) as issued_qty')
            ->whereNull('returned_at')
            ->groupBy('store_entry_item_id');

        $stockBase = DB::table('store_entry_items as sei')
            ->join('item_categories as ic', 'ic.id', '=', 'sei.item_category_id')
            ->leftJoinSub($issuedSub, 'iss', function ($j) {
                $j->on('iss.store_entry_item_id', '=', 'sei.id');
            })
            ->selectRaw("
            ic.type as type,
            COALESCE(SUM(sei.qty - COALESCE(iss.issued_qty,0)),0) as remaining_qty,
            COALESCE(SUM((sei.qty - COALESCE(iss.issued_qty,0)) * sei.rate),0) as remaining_value
        ")
            ->groupBy('ic.type')
            ->get();

        $stockByType = [
            'consumable_qty'   => (float) ($stockBase->firstWhere('type', 0)->remaining_qty ?? 0),
            'consumable_value' => (float) ($stockBase->firstWhere('type', 0)->remaining_value ?? 0),
            'non_qty'          => (float) ($stockBase->firstWhere('type', 1)->remaining_qty ?? 0),
            'non_value'        => (float) ($stockBase->firstWhere('type', 1)->remaining_value ?? 0),
        ];

        // -------------------------
        // Supplier spend (Top 8)
        // -------------------------
        $supplierSpendQ = DB::table('store_entry_items as sei')
            ->join('store_entries as se', 'se.id', '=', 'sei.store_entry_id')
            ->leftJoin('suppliers as s', 's.id', '=', 'se.supplier_id');

        if ($from && $to) {
            $supplierSpendQ->whereBetween('se.purchase_date', [$from, $to]);
        }

        $topSuppliers = $supplierSpendQ
            ->selectRaw("COALESCE(s.name,'(Unknown)') as supplier, COALESCE(SUM(sei.total_price),0) as amount")
            ->groupBy('supplier')
            ->orderByDesc('amount')
            ->limit(8)
            ->get();

        // -------------------------
        // Category spend (Top 8)
        // -------------------------
        $categorySpendQ = DB::table('store_entry_items as sei')
            ->join('categories as c', 'c.id', '=', 'sei.category_id');

        $topCategories = $categorySpendQ
            ->selectRaw("COALESCE(c.name,'(No Category)') as category, COALESCE(SUM(sei.total_price),0) as amount")
            ->groupBy('category')
            ->orderByDesc('amount')
            ->limit(8)
            ->get();

        // -------------------------
        // Low stock products (Top 12)
        // -------------------------
        $lowStock = DB::table('store_entry_items as sei')
            ->join('products as p', 'p.id', '=', 'sei.product_id')
            ->leftJoinSub($issuedSub, 'iss', function ($j) {
                $j->on('iss.store_entry_item_id', '=', 'sei.id');
            })
            ->selectRaw("
            p.id as product_id,
            p.name as product_name,
            COALESCE(SUM(sei.qty - COALESCE(iss.issued_qty,0)),0) as remaining_qty
        ")
            ->groupBy('p.id', 'p.name')
            ->orderBy('remaining_qty', 'asc')
            ->limit(12)
            ->get();

        // -------------------------
        // Recent store outs (last 8)
        // -------------------------
        $recentOuts = StoreOut::with(['department', 'employee'])
            ->latest()
            ->limit(8)
            ->get();

        return view(
            'Backend.dashboard.index',
            compact(
                'counts',
                'purchaseTotals',
                'stockByType',
                'topSuppliers',
                'topCategories',
                'lowStock',
                'recentOuts',
                'from',
                'to',
                'admin'
            )
        );

        
    }


}
