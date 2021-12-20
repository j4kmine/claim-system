<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Illuminate\Http\Request;
use Validator;
use Log;

class PackageController extends Controller
{
    public function packages(Request $request)
    {   
        $packages = Package::get();
        return response()->json(['packages' => $packages], 200);
    }
}