<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use JamesDordoy\LaravelVueDatatable\Http\Resources\DataTableCollectionResource;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;

class CompanyUserController extends Controller
{
    use ApiResponser;

    public function index(Request $request)
    {

        $length = $request->input('length');
        $orderBy = $request->input('column'); //Index
        $orderByDir = $request->input('dir', 'asc');
        $searchValue = $request->input('search');
        $query = User::eloquentQuery($orderBy, $orderByDir, $searchValue,[
            "company",
        ])
        ->where('company_id', Auth::user()->company_id)
        ->paginate($length);
        return new DataTableCollectionResource($query);
    }

    public function store(Request $request)
    {
        $attr = $request->validate([
            'name' => 'required',
            'email' => 'required|unique:users,email',
            'role' => 'required|in:' . implode(',', User::ROLES)
        ]);

        $company = Auth::user()->company;

        $newUser = User::create([
            'name' => $attr['name'],
            'email' => $attr['email'],
            'password' => bcrypt('carfren!234'),
            'category' => $company->type,
            'role' => $attr['role'],
            'status' => 'inactive',
            'company_id' => $company->id
        ]);

        return $this->success($newUser, Response::HTTP_CREATED);
    }
}
