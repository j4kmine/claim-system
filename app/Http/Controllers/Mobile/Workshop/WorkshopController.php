<?php

namespace App\Http\Controllers\Mobile\Workshop;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class WorkshopController extends Controller
{
    use ApiResponser;

    public function index()
    {
        return $this->success(
            Company::where('type', 'workshop')->get()
        );
    }
}
