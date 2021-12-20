<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use JamesDordoy\LaravelVueDatatable\Http\Resources\DataTableCollectionResource;
use Validator;
use Illuminate\Support\Facades\Password;
use Log;

class CustomerController extends Controller
{
    use ApiResponser;

    public function customers(Request $request)
    {
        $length = $request->input('length');
        $orderBy = $request->input('column'); //Index
        $orderByDir = $request->input('dir', 'asc');
        $searchValue = $request->input('search');

        $query = Customer::eloquentQuery($orderBy, $orderByDir, $searchValue);
        $data = $query->paginate($length);

        return new DataTableCollectionResource($data);
    }

    public function detail(Request $request, $id){
        if (is_null($id)){
            return response()->json("Not Found", 404);
        }
        $customer = Customer::whereId($id)->first();
        return $customer;
    }

    public function store(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required|exists:customers,id'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }
        // TODO:: Check permission
        $customer = Customer::where('id', $request->id)->first();
        return response()->json(['customer' => $customer], 200);
    }

    /**
     * Get All Customers
     * 
     * @param page for pagination
     */
    public function index(Request $request)
    {
        $length = $request->input('length');
        $orderBy = $request->input('column'); //Index
        $orderByDir = $request->input('dir', 'asc');
        $searchValue = $request->input('search');

        $query = Customer::eloquentQuery($orderBy, $orderByDir, $searchValue);
        $query = $query->paginate($length);

        return new DataTableCollectionResource($query);
    }

    /**
     * Get detail for a customer
     * with its vehicles, warranties, and services
     */
    public function show(Request $request, Customer $customer)
    {
        $customer = Customer::with('vehicles.warranty')
            ->with('vehicles.services')
            ->find($customer->id);

        return $this->success($customer);
    }

    public function update(Request $request, Customer $customer)
    {
        $attr = $request->validate([
            'name' => 'nullable',
            'nric_uen' => 'nullable',
            'date_of_birth' => 'nullable|date_format:Y-m-d',
            'phone' => 'nullable',
            'email' => 'nullable|email|unique:customers,email,'.$customer->id,
            'status' => 'nullable|in:active,inactive'
        ]);

        $customer->update($attr);

        return $this->success($customer);
    }

    public function vehicles(Customer $customer)
    {
        $customer = Customer::with('vehicles.warranties')
            ->with('vehicles.services')
            ->with('customer_vehicle')
            ->find($customer->id);

        return $this->success($customer);
    }
}
