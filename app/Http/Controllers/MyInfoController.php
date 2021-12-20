<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use App\Models\Customer;
use Ziming\LaravelMyinfoSg\LaravelMyinfoSgFacade as LaravelMyinfoSg;

class MyInfoController extends Controller
{
    public function index(Request $request)
    {   
        // Get the Singpass URI and redirect to there
        return redirect(LaravelMyinfoSg::generateAuthoriseApiUrl(uniqid()));
    }

    public function callback(Request $request) {
        // Get the Myinfo person data in an array with 'data' key
        try{
            $personData = LaravelMyinfoSg::getMyinfoPersonData($request->code);

            // If you didn't want to return a json response with the person information in the 'data' key. You can do this
            $data = $personData['data'];
            /*
            "uinfin":{"lastupdated":"2021-06-24","source":"1","classification":"C","value":"S3100052A"}, D
            "name":{"lastupdated":"2021-06-24","source":"1","classification":"C","value":"TAN HENG HUAT"}, D
            "sex":{"lastupdated":"2021-06-24","code":"F","source":"1","classification":"C","desc":"FEMALE"}, D
            "dob":{"lastupdated":"2021-06-24","source":"1","classification":"C","value":"1998-06-06"}, D
            "nationality":{"lastupdated":"2021-06-24","code":"SG","source":"1","classification":"C","desc":"SINGAPORE CITIZEN"}, D
            "occupation":{"lastupdated":"2021-06-24","code":"","source":"2","classification":"C","desc":""}, D
            "mobileno":{"lastupdated":"2021-06-24","source":"4","classification":"C","areacode":{"value":"65"},"prefix":{"value":"+"},"nbr":{"value":"97399245"}}, D
            "email":{"lastupdated":"2021-06-24","source":"4","classification":"C","value":"myinfotesting@gmail.com"}, D
            "regadd":{"country":{"code":"SG","desc":"SINGAPORE"},"unit":{"value":"128"},"street":{"value":"BEDOK NORTH AVENUE 4"},"lastupdated":"2021-06-24","block":{"value":"102"},"source":"1","postal":{"value":"460102"},"classification":"C","floor":{"value":"09"},"type":"SG","building":{"value":"PEARL GARDEN"}},
            
            "drivinglicence":{"qdl":{"validity":{"code":"V","desc":"VALID"},
            "classes":[{"class":{"value":"2A"},"issuedate":{"value":"2018-06-06"}},{"class":{"value":"3A"},"issuedate":{"value":"2018-06-06"}},{"class":{"value":"3A"},"issuedate":{"value":"2018-06-06"}}]},"lastupdated":"2021-06-24","source":"1","classification":"C"},

            "vehicles":[
                {"vehicleno":{"value":"SDF1235A"},"lastupdated":"2021-06-24","source":"1","classification":"C","type":{"value":"Station Wagon\/Jeep\/Land Rover"},"make":{"value":"KIA"},"model":{"value":"KIA SEDONA"},"chassisno":{"value":"ZC11S1735800"},"engineno":{"value":"M13A1837453"},"yearofmanufacture":{"value":"2013"},"firstregistrationdate":{"value":"2013-05-19"}}]}  
            */
            
            $address = "";
            if($data['regadd']['block']['value'] != ''){
                $address = $data['regadd']['block']['value'] . " ";
            }
            if($data['regadd']['street']['value'] != '') {
                $address .= $data['regadd']['street']['value'] . " ";
            }
            if($data['regadd']['floor']['value'] != '') {
                if($data['regadd']['unit']['value'] != '') {
                    $address .= "#" . $data['regadd']['floor']['value'] . "-" . $data['regadd']['floor']['value'] . " ";
                } else {
                    $address .= $data['regadd']['floor']['value'] . " ";
                }
            } else {
                if($data['regadd']['unit']['value'] != '') {
                    $address .= $data['regadd']['unit']['value'] . " ";
                }
            }
            if($data['regadd']['building']['value'] != '') {
                $address .= $data['regadd']['building']['value'] . " ";
            }

            $driving_classes = "";
            $compare_validity = Carbon::now();
            $driving_validity = '';

            for($i = 0; $i < sizeof($data['drivinglicence']['qdl']['classes']); $i++){
                $driving_classes .= $data['drivinglicence']['qdl']['classes'][$i]['class']['value'] . ",";
                $temp_validity = Carbon::parse($data['drivinglicence']['qdl']['classes'][$i]['issuedate']['value']);
                if($temp_validity->lt($compare_validity)){
                    $driving_validity = $temp_validity->format('d/m/Y');
                    $compare_validity = $temp_validity;
                }
            }

            $driving_classes = rtrim($driving_classes, ',');

            $vehicle_nos = '';
            $vehicle_types = '';
            $vehicle_makes = '';
            $vehicle_models = '';
            $vehicle_chassises = '';
            $vehicle_engines = '';
            $vehicle_manufactures = '';
            $vehicle_registrations = '';
            
            for($i = 0; $i < sizeof($data['vehicles']); $i++){
                // $vehicle_types = intval($data['vehicles'][$i]['nooftransfers']['value']) == 0 ? 'new' : 'preowned';
                if($i > 0){
                    $vehicle_nos =   $vehicle_nos.",".$data['vehicles'][$i]['vehicleno']['value'];
                    $vehicle_types = $vehicle_types.",".intval($data['vehicles'][$i]['nooftransfers']['value']) == 0 ? 'new' : 'preowned';
                    $vehicle_makes = $vehicle_makes.",".$data['vehicles'][$i]['make']['value'];
                    $vehicle_models = $vehicle_models.",".$data['vehicles'][$i]['model']['value'];
                    $vehicle_chassises = $vehicle_chassises.",".$data['vehicles'][$i]['chassisno']['value'];
                    $vehicle_engines = $vehicle_engines.",".$data['vehicles'][$i]['engineno']['value'];
                    $vehicle_manufactures = $vehicle_manufactures.",".$data['vehicles'][$i]['yearofmanufacture']['value'];
                    $vehicle_registrations = $vehicle_registrations.",".$data['vehicles'][$i]['originalregistrationdate']['value'];
                }else{
                    $vehicle_nos = $data['vehicles'][$i]['vehicleno']['value'];
                    $vehicle_types = intval($data['vehicles'][$i]['nooftransfers']['value']) == 0 ? 'new' : 'preowned';
                    $vehicle_makes = $data['vehicles'][$i]['make']['value'];
                    $vehicle_models = $data['vehicles'][$i]['model']['value'];
                    $vehicle_chassises = $data['vehicles'][$i]['chassisno']['value'];
                    $vehicle_engines = $data['vehicles'][$i]['engineno']['value'];
                    $vehicle_manufactures = $data['vehicles'][$i]['yearofmanufacture']['value'];
                    $vehicle_registrations = $data['vehicles'][$i]['originalregistrationdate']['value'];
                }
              
            }

            $vehicle_nos = rtrim($vehicle_nos, ',');
            $vehicle_types = rtrim($vehicle_types, ',');
            $vehicle_makes = rtrim($vehicle_makes, ',');
            $vehicle_models = rtrim($vehicle_models, ',');
            $vehicle_chassises = rtrim($vehicle_chassises, ',');
            $vehicle_engines = rtrim($vehicle_engines, ',');
            $vehicle_manufactures = rtrim($vehicle_manufactures, ',');
            $vehicle_registrations = rtrim($vehicle_registrations, ',');
            $occupation = isset($data['occupation']) ? $data['occupation']['desc'] : "";

            return redirect('/singpass/success?uinfin=' . $data['uinfin']['value'] 
                            . '&name=' . $data['name']['value'] 
                            . '&gender=' . $data['sex']['code'] 
                            . '&dob=' . Carbon::parse($data['dob']['value'])->format('d/m/Y') 
                            . '&nationality=' . $data['nationality']['code'] 
                            . '&occupation=' . $occupation
                            . '&phone=' . $data['mobileno']['nbr']['value'] 
                            . '&email=' . $data['email']['value']
                            . '&address=' . $address
                            . '&driving_class=' . $driving_classes
                            . '&driving_validity=' . $driving_validity
                            . '&vehicle_nos=' . $vehicle_nos
                            . '&vehicle_types=' . $vehicle_types
                            . '&vehicle_makes=' . $vehicle_makes
                            . '&vehicle_models=' . $vehicle_models
                            . '&vehicle_chassises=' . $vehicle_chassises
                            . '&vehicle_engines=' . $vehicle_engines
                            . '&vehicle_manufactures=' . $vehicle_manufactures
                            . '&vehicle_registrations=' . $vehicle_registrations);
        } catch (\Exception $e) {
            Log::debug($e->getMessage());
            return response()->json(['message' => 'An error has occured.'], 422);    
        }
    }

}