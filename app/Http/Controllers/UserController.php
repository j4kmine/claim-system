<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ActionLog;
use Illuminate\Http\Request;
use App\Models\Company;
use JamesDordoy\LaravelVueDatatable\Http\Resources\DataTableCollectionResource;
use Validator;
use Auth;
use Hash;
use Illuminate\Support\Facades\Password;

class UserController extends Controller
{
    public function users(Request $request)
    {   
        $length = $request->input('length');
        $orderBy = $request->input('column'); //Index
        $orderByDir = $request->input('dir', 'asc');
        $searchValue = $request->input('search');
        
        $query = User::eloquentQuery($orderBy, $orderByDir, $searchValue,[
            "company",
        ]);
        if(Auth::user()->category != 'all_cars'){
           $query = $query->where('company_id', Auth::user()->company_id);
           if (Auth::user()->category == 'dealer') $query = $query->where('role', '!=', 'support_staff');
        }
        $data = $query->paginate($length);
        
        return new DataTableCollectionResource($data);
    }

    public function companyUsers(Request $request, $company_id)
    {   
        $length = $request->input('length');
        $orderBy = $request->input('column'); //Index
        $orderByDir = $request->input('dir', 'asc');
        $searchValue = $request->input('search');
        
        $query = User::eloquentQuery($orderBy, $orderByDir, $searchValue,[
            "company",
        ])->where('company_id', $company_id);
        
        $data = $query->paginate($length);
        
        return new DataTableCollectionResource($data);
    }

    public function ownself(Request $request){
        if(Auth::user()->role == 'admin'){
            $user = User::with('company')->where('id',  Auth::user()->id)->first();
        } else {
            $user = Auth::user();
        }
        return response()->json($user, 200);
    }

    public function changePassword(Request $request){
        $valid = Validator::make($request->all(), [
            'old_password' => 'required',
            'password' => 'required|confirmed|min:6'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }

        $user = Auth::user();
        if(Hash::check($request->old_password, $user->password)){
            $user->password = bcrypt($request->password); 
            $user->save();
            return response()->json(['message' => 'Password updated successfully.'], 200);
        } else {
            return response()->json(['message' => 'Wrong old password.'], 422);
        }
    }

    public function changeProfile(Request $request){
        $valid = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'email' => 'required|email'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }
        $user = Auth::user();
        $email = User::where('email' , $request->email)->where('id', '!=', $user->id)->first();
        if($email != null){
            return response()->json(['message' => 'Email has already been taken.'], 422);
        }
        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();
        return response()->json(['message' => 'Profile updated successfully.'], 200);
    }

    public function user(Request $request){
        $valid = Validator::make($request->all(), [
            'id' => 'required|exists:users,id'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }
        // TODO:: Check permission
        $user = User::where('id', $request->id)->first();
        return response()->json(['user' => $user], 200);
    }

    public function edit(Request $request){
        $valid = Validator::make($request->all(), [
            'id' => 'required|exists:users,id',
            'name' => 'required|max:255',
            //'email' => 'required|email|max:255',
            /*'role' => 'required|in:support_staff,admin',
            'category' => 'required|in:all_cars,workshop,surveyor,insurer',
            'company_id' => 'nullable|exists:companies,id',*/
            'status' => 'required|in:active,inactive'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }
        /*
        if(!isset($request->company_id) && $request->category != 'all_cars'){
            return response()->json(['message' => 'Company cannot be empty.'], 422);
        }*/

        // TODO:: Check permission
        $user = User::where('id', $request->id)->first();
        $user->name = $request->name;
        //$user->email = $request->email;
        /*$user->role = $request->role;
        $user->category = $request->category;
        $user->company_id = $request->company_id;*/
        $user->status = $request->status;     
        $user->notification_email = $request->notification_email;     
        $user->save();

        if($request->status == 'inactive'){
            $user->tokens()->delete();
        }
            
        return response()->json(['message' => 'Successfully edited user.'], 200);
    }

    public function create(Request $request){
        $valid = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'role' => 'required|in:support_staff,admin,salesperson',
            'category' => 'required|in:all_cars,workshop,surveyor,insurer,dealer',
            'company_id' => 'nullable|exists:companies,id',
            'status' => 'required|in:active,inactive'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }

        if(empty($request->company_id) && $request->category != 'all_cars'){
            return response()->json(['message' => 'Company cannot be empty.'], 422);
        }

        if($request->category != 'dealer' && $request->role == 'salesperson'){
            return response()->json(['message' => 'Only dealer can have salesperson.'], 422);
        }

        if(($request->category != Auth::user()->category || $request->company_id != Auth::user()->company_id) && Auth::user()->category != 'all_cars'){
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'password' => bcrypt('carfren!234'),
            'category' => $request->category,
            'company_id' => $request->company_id,
            'status' => $request->status
        ]);
        
        Password::sendResetLink(
            $request->only('email')
        );
        //$user->sendPasswordResetNotification(app('auth.password.broker')->createToken($user));
        
        return response()->json(['message' => 'Successfully created user.'], 201);
    }

    public function companies(Request $request){
        $valid = Validator::make($request->all(), [
            'category' => 'required|in:all_cars,workshop,surveyor,insurer,dealer'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }

        if ($request->category == "all_cars"){
            return response()->json(['companies' => []], 200);
        }
        
        $companies = Company::where('type', $request->category)->get();
        return response()->json(['companies' => $companies], 200);
    }
}