<?php

namespace App\Http\Controllers\Mobile\Warranty;

use App\Http\Controllers\Controller;
use App\Models\WarrantyPrice;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;
use DB;

class WarrantyPriceController extends Controller
{
    use ApiResponser;

    public function index(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'car_make' => 'required',
            'car_model' => 'required',
            'fuel' => 'required|in:hybrid,non_hybrid',
            'type' => 'required|in:preowned,new',
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->toArray(), 422);
        }

        $priceLists = WarrantyPrice::with('insurer')->where('make', 'like', '%' . $request->car_make . '%')
            ->where('model', 'like', '%' . $request->car_model . '%')
            ->where('fuel', $request->fuel)
            ->where('type', $request->type)->get();  

        if (count($priceLists) == 0) return response()->json(['message' => 'Car does not exist, please input another car.'], 422);

        return $this->success($priceLists);
    }

    public function show(Request $request, WarrantyPrice $price)
    {
        $price = WarrantyPrice::with('insurer')->where('id', $price->id)->first();
        return $this->success($price);
    }

    public function makes(){
        $makes = WarrantyPrice::groupBy('make')->select('make', DB::raw('count(*) as total'))->get();
        return $this->success($makes);   
    }

    public function models(Request $request){
        $valid = Validator::make($request->all(), [
            'make' => 'required',
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->toArray(), 422);
        }
        
        $models = WarrantyPrice::where('make', $request->make)->groupBy('model')->select('model', DB::raw('count(*) as total'))->get();
        return $this->success($models);   
    }
}
