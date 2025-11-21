<?php

namespace App\Http\Controllers;

use App\Models\StoreOut;
use Illuminate\Http\Request;

class EmployeeStoreOutController extends Controller
{
    public function index(Request $request)
    {
        $user = auth('employee')->user();

        $q = StoreOut::query()
            ->with('items')
            ->where('employee_id', $user->id)
            ->latest('store_out_date_bs');

        $outs = $q->paginate(15);

        return view('employee.store_out.index', compact('outs'));
    }
}
