<?php

namespace App\Http\Controllers\Mobile\Company;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class CompanyServiceTypeController extends Controller
{
    use ApiResponser;

    public function index(Company $company)
    {
        $serviceTypes = $company->service_types()
            ->where('status', 'active')
            ->get();

        return $this->success($serviceTypes);
    }
}
