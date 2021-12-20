<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\ServiceType;
use App\Models\User;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use JamesDordoy\LaravelVueDatatable\Http\Resources\DataTableCollectionResource;
use Symfony\Component\HttpFoundation\Response;

class ServiceTypeController extends Controller
{
    use ApiResponser;

    public function index(Request $request)
    {
        $length = $request->input('length');
        $orderBy = $request->input('column'); //Index
        $orderByDir = $request->input('dir', 'asc');
        $searchValue = $request->input('search');
        $query = ServiceType::eloquentQuery($orderBy, $orderByDir, $searchValue)
            ->where('workshop_id', Auth::user()->company_id)
            ->paginate($length);
        return new DataTableCollectionResource($query);
    }

    public function store(Request $request)
    {
        $attr = $request->validate([
            'name' => 'required|string|unique:service_types',
            'status' => 'nullable|in:active,inactive',
            'description' => 'nullable|string',
            'color' => 'nullable|string'
        ]);

        $company = Auth::user()->company;

        $type = ServiceType::create([
            'workshop_id' => $company->id,
            'name' => $attr['name'],
            'status' => isset($attr['status']) ? $attr['status'] : 'active',
            'description' => $attr['description'],
            'color' => $attr['color']
        ]);
        $type->setAttribute('message', "Service Types has Created!");
        return $this->success(
            $type,
            Response::HTTP_CREATED
        );
    }

    public function detail(Request $request, ServiceType $type)
    {
        return $type;
    }

    public function update(Request $request, ServiceType $type)
    {
        $attr = $request->validate([
            'name' => 'required|string',
            'status' => 'nullable|in:active,inactive',
            'description' => 'nullable|string',
            'color' => 'nullable|string'
        ]);

        $type->update($attr);

        return $this->success($type);
    }
}
