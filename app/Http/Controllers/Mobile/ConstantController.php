<?php

namespace App\Http\Controllers\Mobile;

use ReflectionClass;
use App\Models\Driver;
use App\Models\Reports;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ConstantController extends Controller
{
    use ApiResponser;

    public function index($class)
    {
        $class = ucfirst($class);

        $refl = new ReflectionClass('App\Models\\' . $class);

        return $this->success($refl->getConstants());
    }
}
