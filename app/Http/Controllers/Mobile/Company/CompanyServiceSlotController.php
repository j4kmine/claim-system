<?php

namespace App\Http\Controllers\Mobile\Company;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class CompanyServiceSlotController extends Controller
{
    use ApiResponser;

    public function index(Company $company,$date)
    {
    	$day = date('l',strtotime($date));
        return $this->success($company
            ->servicing_slots()
            ->where('status', 'active')
            ->where('day', $day)
            ->get());
    }
}
