<?php

namespace App\Services;

use Carbon\Carbon;
use RuntimeException;

class BSDateService
{
    /**
     * Map of BS year -> [1..12] month lengths.
     * IMPORTANT: You must fill this for every supported year (e.g., 1970..2100 BS).
     * I include a few sample years so it runs; extend it by loading from storage/app/bs_months.json.
     */
    protected array $daysInMonths = [
        //            1  2  3  4  5  6  7  8  9 10 11 12
        2080 => [0,  31,31,32,31,32,31,30,29,30,29,30,30],
        2081 => [0,  31,32,31,32,31,30,30,30,29,30,29,30],
        2082 => [0,  31,31,32,31,32,31,30,29,30,29,30,30],
        2083 => [0,  31,32,31,32,31,30,30,30,29,30,29,30],
        2084 => [0,  31,31,32,31,32,31,30,29,30,29,30,30],
        // add more years or call loadMapFromDisk() in __construct()
    ];

    /**
     * Anchor pair commonly used in Nepali calendar conversions:
     * 2000-01-01 BS = 1943-04-13 AD
     */
protected string $anchorBs = '2080-01-01';
protected string $anchorAd = '2023-04-14';

    public function __construct()
    {
        // If you save the full dataset at storage/app/bs_months.json, uncomment:
        $this->loadMapFromDisk();
    }

    protected function loadMapFromDisk(): void
    {
        $file = storage_path('app/bs_months.json');
        if (is_file($file)) {
            $json = json_decode(file_get_contents($file), true);
            if (is_array($json) && !empty($json)) {
                $this->daysInMonths = $json;
            }
        }
    }

    public function adToBs(\DateTimeInterface $ad): array
    {
        $adNepal = Carbon::instance((new Carbon($ad))->tz('Asia/Kathmandu'))->startOfDay();
        $anchor  = Carbon::parse($this->anchorAd, 'Asia/Kathmandu')->startOfDay();
        $diffDays = $anchor->diffInDays($adNepal, false);

        [$y, $m, $d] = array_map('intval', explode('-', $this->anchorBs));

        $step = $diffDays >= 0 ? 1 : -1;
        $remaining = abs($diffDays);

        while ($remaining > 0) {
            $d += $step;
            if ($step > 0) {
                if ($d > $this->daysInMonth($y, $m)) {
                    $d = 1; $m++; if ($m > 12) { $m = 1; $y++; }
                }
            } else {
                if ($d < 1) {
                    $m--; if ($m < 1) { $m = 12; $y--; }
                    $d = $this->daysInMonth($y, $m);
                }
            }
            $remaining--;
        }

        return ['year' => $y, 'month' => $m, 'day' => $d];
    }

    public function bsToAd(int $y, int $m, int $d): \DateTimeImmutable
    {
        $anchorAd = Carbon::parse($this->anchorAd, 'Asia/Kathmandu')->startOfDay();
        [$ay, $am, $adDay] = array_map('intval', explode('-', $this->anchorBs));

        $days = 0;
        if ($y === $ay && $m === $am) {
            $days = $d - $adDay;
        } else {
            // decide direction by comparing BS (target) vs BS (anchor)
            $backward = ($y < $ay) || ($y == $ay && $m < $am) || ($y == $ay && $m == $am && $d < $adDay);

            if ($backward) {
                while (!($ay == $y && $am == $m && $adDay == $d)) {
                    $adDay -= 1;
                    if ($adDay < 1) {
                        $am -= 1; if ($am < 1) { $am = 12; $ay -= 1; }
                        $adDay = $this->daysInMonth($ay, $am);
                    }
                    $days -= 1;
                }
            } else {
                while (!($ay == $y && $am == $m && $adDay == $d)) {
                    $adDay += 1;
                    if ($adDay > $this->daysInMonth($ay, $am)) {
                        $adDay = 1; $am += 1; if ($am > 12) { $am = 1; $ay += 1; }
                    }
                    $days += 1;
                }
            }
        }

        return Carbon::instance($anchorAd)->addDays($days)->toDateImmutable();
    }

    public function daysInMonth(int $y, int $m): int
    {
        if (!isset($this->daysInMonths[$y])) {
            throw new RuntimeException("BS year not supported: $y");
        }
        if (!isset($this->daysInMonths[$y][$m])) {
            throw new RuntimeException("BS month not supported: $y-$m");
        }
        return (int) $this->daysInMonths[$y][$m];
    }

    /**
     * Returns 6x7 cells grouped by weeks for a given BS month.
     * Each cell: ['bsY','bsM','bsD','ad','isCurrentMonth'=>bool]
     */
    public function monthGrid(int $y, int $m): array
    {
        $startAd = $this->bsToAd($y, $m, 1);
        $startWeekday = (int)date('w', $startAd->getTimestamp()); // 0=Sun..6=Sat

        $days = $this->daysInMonth($y, $m);

        // previous month
        $pm = $m - 1; $py = $y; if ($pm < 1) { $pm = 12; $py--; }
        $prevDays = $this->daysInMonth($py, $pm);

        $cells = [];
        for ($i=0; $i<$startWeekday; $i++) {
            $d = $prevDays - ($startWeekday - 1 - $i);
            $ad = $this->bsToAd($py, $pm, $d);
            $cells[] = ['bsY'=>$py,'bsM'=>$pm,'bsD'=>$d,'ad'=>$ad->format('Y-m-d'),'isCurrentMonth'=>false];
        }

        // current month
        for ($d=1; $d<=$days; $d++) {
            $ad = $this->bsToAd($y, $m, $d);
            $cells[] = ['bsY'=>$y,'bsM'=>$m,'bsD'=>$d,'ad'=>$ad->format('Y-m-d'),'isCurrentMonth'=>true];
        }

        // next month
        $nm = $m + 1; $ny = $y; if ($nm > 12) { $nm = 1; $ny++; }
        $nextD = 1;
        while (count($cells) < 42) {
            $ad = $this->bsToAd($ny, $nm, $nextD++);
            $cells[] = ['bsY'=>$ny,'bsM'=>$nm,'bsD'=>$nextD-1,'ad'=>$ad->format('Y-m-d'),'isCurrentMonth'=>false];
        }

        return array_chunk($cells, 7);
    }
}
