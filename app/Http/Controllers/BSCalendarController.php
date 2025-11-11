<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BSDateService;
use Carbon\Carbon;

class BSCalendarController extends Controller
{
    public function __construct(private BSDateService $svc) {}

public function bsMonth(Request $req)
{
    $y = (int)$req->query('year');
    $m = (int)$req->query('month');

    $weeks = $this->svc->monthGrid($y, $m); // 6 arrays of 7 each
    // flatten to 42 cells
    $grid = [];
    foreach ($weeks as $w) { foreach ($w as $cell) { $grid[] = $cell; } }

    return response()->json([
        'year'  => $y,
        'month' => $m,
        'grid'  => $grid,  // 👈 picker uses this
        'weeks' => $weeks, // optional (kept for other views)
    ]);
}


    public function adToBs(Request $req)
    {
        $date = Carbon::parse(
            $req->query('date', now('Asia/Kathmandu')->toDateString()),
            'Asia/Kathmandu'
        );
        $bs = $this->svc->adToBs($date);
        return response()->json([
            'input' => ['ad' => $date->toDateString()],
            'result' => ['bs' => sprintf('%04d-%02d-%02d', $bs['year'], $bs['month'], $bs['day'])]
        ]);
    }

    public function bsToAd(Request $req)
    {
        $y = (int)$req->query('year');
        $m = (int)$req->query('month');
        $d = (int)$req->query('day');

        $ad = $this->svc->bsToAd($y, $m, $d);
        return response()->json([
            'input' => ['bs' => sprintf('%04d-%02d-%02d', $y, $m, $d)],
            'result' => ['ad' => $ad->format('Y-m-d')]
        ]);
    }
}
