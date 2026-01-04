<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function admin(Request $request)
    {
        // ---------- Filters (AD dates) ----------
        $from = $request->filled('from') ? Carbon::parse($request->from)->startOfDay() : now()->subDays(30)->startOfDay();
        $to   = $request->filled('to')   ? Carbon::parse($request->to)->endOfDay()     : now()->endOfDay();

        $departmentId = $request->get('department_id');
        $categoryId   = $request->get('category_id');

        // ---------- KPI: Store In ----------
        $inBase = DB::table('store_entry_items as sei')
            ->join('store_entries as se', 'se.id', '=', 'sei.store_entry_id')
            ->whereBetween('se.created_at', [$from, $to]);

        if ($categoryId) {
            $inBase->where('sei.category_id', $categoryId);
        }

        $inAgg = (clone $inBase)
            ->selectRaw('
                COALESCE(SUM(sei.qty),0) as in_qty,
                COALESCE(SUM(sei.total_price),0) as in_amount
            ')
            ->first();

        $totalEntries = DB::table('store_entries')
            ->whereBetween('created_at', [$from, $to])
            ->count();

        // ---------- KPI: Store Out (Active + Returned) ----------
        $outBase = DB::table('store_out_items as soi')
            ->join('store_outs as so', 'so.id', '=', 'soi.store_out_id')
            ->whereBetween('so.created_at', [$from, $to]);

        if ($departmentId) {
            $outBase->where('so.department_id', $departmentId);
        }
        if ($categoryId) {
            $outBase->where('soi.category_id', $categoryId);
        }

        $outActiveAgg = (clone $outBase)
            ->whereNull('soi.returned_at')
            ->selectRaw('
                COALESCE(SUM(soi.qty),0) as out_qty,
                COALESCE(SUM(COALESCE(soi.total_price, soi.qty * COALESCE(soi.rate,0))),0) as out_amount
            ')
            ->first();

        $outReturnedAgg = (clone $outBase)
            ->whereNotNull('soi.returned_at')
            ->selectRaw('
                COALESCE(SUM(soi.qty),0) as returned_qty,
                COALESCE(SUM(COALESCE(soi.total_price, soi.qty * COALESCE(soi.rate,0))),0) as returned_amount
            ')
            ->first();

        $totalOuts = DB::table('store_outs')
            ->whereBetween('created_at', [$from, $to])
            ->when($departmentId, fn($q) => $q->where('department_id', $departmentId))
            ->count();

        // ---------- Current Stock (All time) ----------
        // remaining_qty = total_in - total_active_out, grouped by item/product snapshot
        $stockRows = DB::table('store_entry_items as sei')
            ->leftJoin('store_out_items as soi', function ($j) {
                $j->on('soi.store_entry_item_id', '=', 'sei.id')
                  ->whereNull('soi.returned_at');
            })
            ->selectRaw('
                COALESCE(sei.item_name, "") as item_name,
                COALESCE(sei.product_id, 0) as product_id,
                COALESCE(sei.rate, 0) as rate,
                SUM(sei.qty) as in_qty_all,
                COALESCE(SUM(soi.qty),0) as out_qty_all,
                (SUM(sei.qty) - COALESCE(SUM(soi.qty),0)) as remaining_qty
            ')
            ->when($categoryId, fn($q) => $q->where('sei.category_id', $categoryId))
            ->groupBy('sei.item_name', 'sei.product_id', 'sei.rate')
            ->havingRaw('(SUM(sei.qty) - COALESCE(SUM(soi.qty),0)) > 0')
            ->get();

        $currentStockQty = (float) $stockRows->sum('remaining_qty');
        $currentStockValue = (float) $stockRows->sum(function ($r) {
            return (float)$r->remaining_qty * (float)$r->rate;
        });

        // ---------- Top issued items (Active out) ----------
        $topIssued = (clone $outBase)
            ->whereNull('soi.returned_at')
            ->selectRaw('COALESCE(soi.item_name,"(No Name)") as label, SUM(soi.qty) as qty')
            ->groupBy('label')
            ->orderByDesc('qty')
            ->limit(10)
            ->get();

        // ---------- Department consumption (Active out amount) ----------
        $deptSpend = (clone $outBase)
            ->whereNull('soi.returned_at')
            ->leftJoin('departments as d', 'd.id', '=', 'so.department_id')
            ->selectRaw('COALESCE(d.name,"(No Department)") as label,
                        SUM(COALESCE(soi.total_price, soi.qty * COALESCE(soi.rate,0))) as amount')
            ->groupBy('label')
            ->orderByDesc('amount')
            ->limit(10)
            ->get();

        // ---------- Monthly trend (last 12 months) ----------
        // NOTE: Using created_at (AD). Works best on MySQL/MariaDB.
        $months = collect();
        for ($i = 11; $i >= 0; $i--) {
            $months->push(now()->subMonths($i)->format('Y-m'));
        }

        $inMonthly = DB::table('store_entry_items as sei')
            ->join('store_entries as se', 'se.id', '=', 'sei.store_entry_id')
            ->where('se.created_at', '>=', now()->subMonths(12)->startOfMonth())
            ->when($categoryId, fn($q) => $q->where('sei.category_id', $categoryId))
            ->selectRaw("DATE_FORMAT(se.created_at, '%Y-%m') as ym, SUM(sei.total_price) as amount")
            ->groupBy('ym')
            ->pluck('amount', 'ym');

        $outMonthly = DB::table('store_out_items as soi')
            ->join('store_outs as so', 'so.id', '=', 'soi.store_out_id')
            ->where('so.created_at', '>=', now()->subMonths(12)->startOfMonth())
            ->whereNull('soi.returned_at')
            ->when($departmentId, fn($q) => $q->where('so.department_id', $departmentId))
            ->when($categoryId, fn($q) => $q->where('soi.category_id', $categoryId))
            ->selectRaw("DATE_FORMAT(so.created_at, '%Y-%m') as ym,
                        SUM(COALESCE(soi.total_price, soi.qty * COALESCE(soi.rate,0))) as amount")
            ->groupBy('ym')
            ->pluck('amount', 'ym');

        $trendLabels = $months->values()->all();
        $trendIn  = $months->map(fn($m) => (float)($inMonthly[$m] ?? 0))->values()->all();
        $trendOut = $months->map(fn($m) => (float)($outMonthly[$m] ?? 0))->values()->all();

        // ---------- Dropdowns ----------
        $departments = DB::table('departments')->select('id','name')->orderBy('name')->get();
        $categories  = DB::table('item_categories')->select('id','name')->orderBy('name')->get();

        return view('Backend.dashboard.index', [
            'filters' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
                'department_id' => $departmentId,
                'category_id' => $categoryId,
            ],

            'kpi' => [
                'total_entries' => $totalEntries,
                'in_qty' => (float)$inAgg->in_qty,
                'in_amount' => (float)$inAgg->in_amount,
                'total_outs' => $totalOuts,
                'out_qty' => (float)$outActiveAgg->out_qty,
                'out_amount' => (float)$outActiveAgg->out_amount,
                'returned_qty' => (float)$outReturnedAgg->returned_qty,
                'returned_amount' => (float)$outReturnedAgg->returned_amount,
                'stock_qty' => $currentStockQty,
                'stock_value' => $currentStockValue,
            ],

            'charts' => [
                'trend' => [
                    'labels' => $trendLabels,
                    'in' => $trendIn,
                    'out' => $trendOut,
                ],
                'topIssued' => [
                    'labels' => $topIssued->pluck('label')->all(),
                    'values' => $topIssued->pluck('qty')->map(fn($v)=>(float)$v)->all(),
                ],
                'deptSpend' => [
                    'labels' => $deptSpend->pluck('label')->all(),
                    'values' => $deptSpend->pluck('amount')->map(fn($v)=>(float)$v)->all(),
                ],
            ],

            'tables' => [
                'stockTop' => $stockRows->sortByDesc('remaining_qty')->take(12)->values(),
            ],

            'departments' => $departments,
            'categories' => $categories,
        ]);
    }
}
