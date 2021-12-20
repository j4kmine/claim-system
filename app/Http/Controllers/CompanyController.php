<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use JamesDordoy\LaravelVueDatatable\Http\Resources\DataTableCollectionResource;
use Validator;
use Auth;
use App\Models\Insurer;
use Log;

class CompanyController extends Controller
{
    public function appointments(Request $request, $id){
        $length = $request->input('length');
        $orderBy = $request->input('column'); //Index
        $orderByDir = $request->input('dir', 'asc');
        $searchValue = $request->input('search');
        $query = Company::eloquentQuery($orderBy, $orderByDir, $searchValue,[
            "insurer",
        ])->where('insurers.surveyor_id', $id);
        $data = $query->paginate($length);
        
        return new DataTableCollectionResource($data);
    }

    public function surveyorOptions(){
        $surveyors = Company::where('type', 'surveyor')->get();
        return response()->json(['surveyors' => $surveyors], 200);
    }

    public function changeCompanyInfo(Request $request){
        $valid = Validator::make($request->all(), [
            'company_address' => 'max:255',
            'contact_no' => 'required|max:255',
            'contact_person' => 'required|max:255',
            'contact_email' => 'email|max:255',
            'description' => 'max:10000'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }

        $company = Company::where('id', Auth::user()->company_id)->first();
        $company->address = $request->company_address;
        $company->contact_person = $request->contact_person;
        $company->contact_no = $request->contact_no;
        $company->contact_email = $request->contact_email;
        $company->description = $request->description;
        $company->save();

        return response()->json(['message' => 'Company successfully updated'], 200);
    }

    private function companies(Request $request, $type)
    {   
        $length = $request->input('length');
        $orderBy = $request->input('column'); //Index
        $orderByDir = $request->input('dir', 'asc');
        $searchValue = $request->input('search');
        
        $query = Company::eloquentQuery($orderBy, $orderByDir, $searchValue)->where('type', $type);
        $data = $query->paginate($length);
        
        return new DataTableCollectionResource($data);
    }

    private function company(Request $request, $type){
        $valid = Validator::make($request->all(), [
            'id' => 'required|exists:companies,id'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }

        $company = Company::with('details')->where('id', $request->id)->where('type', $type)->first();
        return response()->json(['company' => $company], 200);
    }

    private function edit(Request $request, $type){
        $valid = Validator::make($request->all(), [
            'id' => 'required|exists:companies,id',
            'name' => 'required|max:255',
            'code' => 'required|max:255',
            'acra' => 'max:255',
            'address' => 'max:255',
            'contact_no' => 'required|max:255',
            'contact_person' => 'required|max:255',
            'contact_email' => 'email|max:255',
            'extended_warranty' => 'nullable|boolean',
            'description' => 'max:10000',
            'surveyor_id' => 'nullable|exists:companies,id',
            'status' => 'required|in:active,inactive'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }

        $name = Company::where('name', $request->name)->where('type', $type)->where('id', '!=', $request->id)->first();
        if($name != null){
            return response()->json(['message' => 'The name has already been taken.'], 422);
        }
        $code = Company::where('code', $request->code)->where('type', $type)->where('id', '!=', $request->id)->first();
        if($code != null){
            return response()->json(['message' => 'The code has already been taken.'], 422);
        }
        $company = Company::where('id', $request->id)->where('type', $type)->first();
        if($company == null){
            return response()->json(['message' => ucfirst($type) , ' does not exist.'], 422);
        }

        if($type == 'insurer'){
            if($request->surveyor_id == null){
                return response()->json(['message' => 'Invalid surveyor id.'], 422);
            }
            Insurer::where('insurer_id', $request->id)->update([
                'surveyor_id' => $request->surveyor_id
            ]);
        }

        $company->name = $request->name;
        $company->code = $request->code;
        $company->acra = $request->acra;
        $company->address = $request->address;
        $company->contact_no = $request->contact_no;
        $company->contact_person = $request->contact_person;
        $company->contact_email = $request->contact_email;
        $company->description = $request->description;
        $company->extended_warranty = $request->extended_warranty;
        $company->status = $request->status;
        $company->save();
        
        $users = User::where('company_id', $company->id)->get();
        foreach($users as $user){
            $user->tokens()->delete();
        }
        
        return response()->json(['message' => 'Successfully edited company.'], 200);
    }

    private function create(Request $request, $type){
        $valid = Validator::make($request->all(), [
            'name' => 'required|max:255|unique:companies',
            'code' => 'required|max:255|unique:companies',
            'acra' => 'max:255',
            'address' => 'max:255',
            'contact_no' => 'required|max:255',
            'contact_person' => 'required|max:255',
            'contact_email' => 'email|max:255',
            'extended_warranty' => 'nullable|boolean',
            'description' => 'max:10000',
            'surveyor_id' => 'nullable|exists:companies,id',
            'status' => 'required|in:active,inactive'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }

        if($type == 'insurer' && $request->surveyor_id == null){
            return response()->json(['message' => 'Please select a surveyor'], 422);
        }

        $company = Company::create([
            'name' => $request->name,
            'code' => $request->code,
            'acra' => $request->acra,
            'address' => $request->address,
            'contact_no' => $request->contact_no,
            'contact_person' => $request->contact_person,
            'contact_email' => $request->contact_email,
            'description' => $request->description,
            'extended_warranty' => $request->extended_warranty,
            'status' => $request->status,
            'type' => $type
        ]);

        if($type == 'insurer'){
            Insurer::create([
                'insurer_id' => $company->id,
                'surveyor_id' => $request->surveyor_id
            ]);
        }
        
        return response()->json(['message' => 'Successfully created company.'], 201);
    }

    public function dealers(Request $request){
        return $this->companies($request, 'dealer');
    }
    public function surveyors(Request $request){
        return $this->companies($request, 'surveyor');
    }
    public function insurers(Request $request){
        return $this->companies($request, 'insurer');
    }
    public function workshops(Request $request){
        return $this->companies($request, 'workshop');
    }
    public function dealer(Request $request){
        return $this->company($request, 'dealer');
    }
    public function surveyor(Request $request){
        return $this->company($request, 'surveyor');
    }
    public function insurer(Request $request){
        return $this->company($request, 'insurer');
    }
    public function workshop(Request $request){
        return $this->company($request, 'workshop');
    }
    public function editDealer(Request $request){
        return $this->edit($request, 'dealer');
    }
    public function editSurveyor(Request $request){
        return $this->edit($request, 'surveyor');
    }
    public function editInsurer(Request $request){
        return $this->edit($request, 'insurer');
    }
    public function editWorkshop(Request $request){
        return $this->edit($request, 'workshop');
    }
    public function createDealer(Request $request){
        return $this->create($request, 'dealer');
    }
    public function createSurveyor(Request $request){
        return $this->create($request, 'surveyor');
    }
    public function createInsurer(Request $request){
        return $this->create($request, 'insurer');
    }
    public function createWorkshop(Request $request){
        return $this->create($request, 'workshop');
    }
    
}