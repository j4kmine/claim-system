<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Motor;
use App\Models\Proposer; 
use App\Models\MotorDocument;
use Constant;

class GenerateDocument implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $motor;

    public function __construct(Motor $motor)
    {
        //
        $this->motor = $motor;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        $endpoint = env("INSURANCE_URL") . "/restlet/v1/pa/policy/" . $this->motor->policy_no;
        $response = insurance_api($endpoint, [], false);
        $arr = json_decode($response->getBody()->getContents(), true);

        if (!isset($arr["NewbizFeeInfo"])) {
            $endpoint = env("INSURANCE_URL") . "/restlet/v1/pa/ap88/policy/sp/camelapi/AP88_newbiz_CalculatePremium";
            $response = insurance_api($endpoint, $arr);
            $arr = json_decode($response->getBody()->getContents(), true);

            $endpoint = env("INSURANCE_URL") . "/restlet/v1/pa/ap88/policy/sp/camelapi/AP98_newbiz_reinsuranceCedingOut";
            $response = insurance_api($endpoint, $arr['Model']);
            $arr = json_decode($response->getBody()->getContents(), true);

            $endpoint = env("INSURANCE_URL") . "/restlet/v1/pa/referral/policy";
            $response = insurance_api($endpoint, $arr['Model']);
            // $arr = json_decode($response->getBody()->getContents(), true);

            $endpoint = env("INSURANCE_URL") . "/restlet/v1/pa/ap88/policy/sp/api/binding";
            $temp = [];
            $temp['Policy'] = $arr['Model'];
            $response = insurance_api($endpoint, $temp);
            $arr = json_decode($response->getBody()->getContents(), true);

            $endpoint = env("INSURANCE_URL") . "/restlet/v1/pa/ap88/policy/sp/camelapi/AP98_newbiz_Save";
            $temp = [];
            $temp = $arr['Model']['Policy']; 
            $proposer = Proposer::where('id', $this->motor->proposer_id)->first();
            $temp["PolicyCustomerList"][0]["Customer"]["PartyAddressList"][0]["BusinessObjectId"] = 1000000240;
            $temp["PolicyCustomerList"][0]["Customer"]["PartyAddressList"][0]["@type"] = "PartyAddress-PartyAddress";
            $temp["PolicyCustomerList"][0]["Customer"]["PartyAddressList"][0]['Address1'] = $proposer->address;
            $temp["PolicyCustomerList"][0]["Customer"]["PartyAddressList"][0]['Address2'] = "NOT AVAILABLE";
            $temp["PolicyCustomerList"][0]["Customer"]["PartyAddressList"][0]['Address3'] = "NOT AVAILABLE";
            $temp["PolicyCustomerList"][0]["Customer"]["PartyAddressList"][0]['Address4'] = $proposer->nationality;
            $temp["PolicyCustomerList"][0]["Customer"]["PartyAddressList"][0]['PostCode'] = $proposer->postal_code;
            $temp["PolicyCustomerList"][0]["Customer"]["PartyContactList"][0]["BusinessObjectId"] = 1000000242;
            $temp["PolicyCustomerList"][0]["Customer"]["PartyContactList"][0]["@type"] = "PartyContact-PartyContact";
            $temp["PolicyCustomerList"][0]["Customer"]["PartyContactList"][0]['Email'] = $proposer->email;
            $temp["PolicyCustomerList"][0]["Customer"]["PartyContactList"][0]['ConfirmEmail'] = $proposer->email;
            $temp["PolicyCustomerList"][0]["Customer"]["PartyContactList"][0]['HandPhone'] = $proposer->phone;
            $temp['PolicyPaymentInfoList'][0]["BusinessObjectId"] = 520259;
            $temp['PolicyPaymentInfoList'][0]["@type"] = "PolicyPaymentInfo-PolicyPaymentInfo";
            $temp['PolicyPaymentInfoList'][0]['PayMode'] = "109";
            
            $response = insurance_api($endpoint, $temp);
            $arr = json_decode($response->getBody()->getContents(), true);

            $endpoint = env("INSURANCE_URL") . "/restlet/v1/pa/ap88/policy/sp/camelapi/AP98_newbiz_Bound";
            $response = insurance_api($endpoint, $arr['Model']);
            $arr = json_decode($response->getBody()->getContents(), true);

            $endpoint = env("INSURANCE_URL") . "/restlet/v1/pa/ap88/policy/sp/camelapi/AP88_newbiz_Issurance";
            $arr = $arr['Model'];
            $arr['IsRenewable'] = "Y";
            $arr['SMS'] = "N";
            $arr['Telephone'] = "N";
            $arr["EmailAddr"] = $proposer->email;
            $arr['FeeAmount'] = 0;
            $response = insurance_api($endpoint, $arr);
            $arr = json_decode($response->getBody()->getContents(), true);
        }
        $endpoint = env("INSURANCE_URL") . "/restlet/v1/pa/ap98/output/policyPrint";
        $arr = Constant::PRINT_POLICY;
        $arr["PolicyNo"] = $this->motor->ci_no;
        $arr["ReferenceNumber"] = $this->motor->ci_no;
        $arr["TransactionNo"] = $this->motor->policy_no;
        $response = insurance_api($endpoint, $arr);
        $arr = json_decode($response->getBody()->getContents(), true);

        $endpoint = env("INSURANCE_URL") . "/restlet/v1/pa/ap98/output/queryDocIdByTasks";
        $temp = [];
        $temp["TaskIds"] = $arr;
        $response = insurance_api($endpoint, $temp);
        $arr = json_decode($response->getBody()->getContents(), true);
        
        $z = 0;
        foreach ($arr as $file_id) {
            $endpoint = env("INSURANCE_URL") . "/restlet/v1/example/repository/download/" . $file_id;
            $filename = $file_id . '.pdf';
            $file_path = storage_path('app/generated/').$filename;
            $response = insurance_file($endpoint, $file_path);
            $arr = json_decode($response->getBody()->getContents(), true);
            if($z == 0){
                $type = 'schedule';
            } elseif ($z == 1){
                $type = 'ci';
            } elseif ($z == 2){
                $type = 'tax_invoice';
            } elseif ($z == 3){
                $type = 'debit_note';
            } elseif ($z == 4){
                $type = 'policy';
            }
            \Storage::disk('s3')->put('motor/'. $type .'/' . $filename, fopen($file_path, 'r+'));
            $path = \Storage::disk('s3')->url('motor/'. $type .'/' . $filename);
            MotorDocument::create([
                'motor_id' => $this->motor->id,
                'name' => $filename,
                'url' => $path,
                'type' => $type
            ]);
            $z++;
        }
    }
}
