<?php

namespace App\Exports;

use App\Models\SurveyorReport;
use App\Models\Report;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReportsExport implements FromCollection, WithHeadings
{

    public function __construct($role, $type, $fromDate, $toDate, $status)
    {
        $this->role = $role;
        $this->type = $type;
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
        $this->status = $status;
    }

    public function collection()
    {
        $collection = [];
        if($this->role == 'surveyor'){
            $arr = SurveyorReport::with(['vehicle', 'company']);
            if($this->type != ''){
                if($this->fromDate != ''){
                    $arr = $arr->where('claims.'.$this->type, '>=', $this->fromDate);
                }
                if($this->toDate != ''){
                    $arr = $arr->where('claims.'.$this->type, '<=',$this->toDate);
                }
            }
            if($this->status != ''){
                $arr = $arr->where('claims.status', $this->status);
            }
            $arr = $arr->orderBy('claims.id', 'desc')->get()->toArray();
            
            foreach($arr as $report){
                $collection[] = [
                    "Ref No." => $report['ref_no'], 
                    "Car Plate" => $report['vehicle'] != null ? $report['vehicle']['registration_no'] : '', 
                    "Chassis No." => $report['vehicle'] != null ? $report['vehicle']['chassis_no']  : '', 
                    "Insurer" => $report['company'] != null ? $report['company']['name'] : '', 
                    "Policy Certificate No." => $report['policy_certificate_no'], 
                    "No. of Reviews" => $report['surveyor_review_count'] . '',
                    "Status" => $report['status']
                ];
            }
        } else {
            $arr = Report::with(['vehicle', 'company', 'workshop', 'items']);
            if($this->type != ''){
                if($this->fromDate != ''){
                    $arr = $arr->where('claims.'.$this->type, '>=', $this->fromDate);
                }
                if($this->toDate != ''){
                    $arr = $arr->where('claims.'.$this->type, '<=',$this->toDate);
                }
            }
            if($this->status != ''){
                $arr = $arr->where('claims.status', $this->status);
            }
            $arr = $arr->orderBy('claims.id', 'desc')->get()->toArray();

            foreach($arr as $report){
                $collection[] = [
                    "Ref No." => $report['ref_no'],  
                    "Car Plate" => $report['vehicle'] != null ? $report['vehicle']['registration_no'] : '', 
                    "Chassis No." => $report['vehicle'] != null ? $report['vehicle']['chassis_no'] : '',
                    "Make" => $report['vehicle'] != null ? $report['vehicle']['make'] : '', 
                    "Model" => $report['vehicle'] != null ? $report['vehicle']['model'] : '',
                    "Mileage" => $report['vehicle'] != null ? $report['vehicle']['mileage'] : '', 
                    "Vehicle Owner NRIC/UEN" => $report['vehicle'] != null ? $report['vehicle']['nric_uen'] : '', 
                    "Insurer" => $report['company'] != null ? $report['company']['name'] : '', 
                    "Workshop" => $report['workshop'] != null ? $report['workshop']['name'] : '', 
                    "Policy Certificate No." => $report['policy_certificate_no'], 
                    "Policy Coverage Period" => $report['policy_coverage_from'] . " to " . $report['policy_coverage_to'], 
                    "Insured Name" => $report['policy_name'], 
                    "Insured NRIC/UEN" => $report['policy_nric_uen'], 
                    "Claim Item (1)" => $report['items'] != null && $report['items'][0] != null ? $report['items'][0]['item'] : '',
                    "Total Claim Amount" => $report['total_claim_amount'], 
                    "Date of Notification" => $report['date_of_notification'], 
                    "Date of Loss" => $report['date_of_loss'], 
                    "Details of Loss" => $report['cause_of_damage'], 
                    "Creation Date" => $report['created_at'], 
                    "Approved Date" => $report['approved_at'], 
                    "Repaired Date" => $report['repaired_at'],
                    "Remarks" => $report['remarks'], 
                    "Status" => $report['status']
                ];
            }
        }

        return collect($collection)->map(function ($row) {
            return (object) $row;
        });
    }

    public function headings() :array
    {
        if($this->role == 'surveyor'){
            return ["Ref No.", "Car Plate", "Chassis No.", "Insurer", "Policy Certificate No.", "No. of Reviews", "Status"];
        } else {
            return ["Ref No.", "Car Plate", "Chassis No.", "Make", "Model", "Mileage", "Vehicle Owner NRIC/UEN", "Insurer", "Worker", "Policy Certificate No.", "Policy Coverage Period", "Insured Name", "Insured NRIC/UEN", "Claim Item (1)", "Total Claim Amount", "Date of Notification", "Date of Loss", "Details of Loss", "Creation Date", "Approved Date", "Repaired Date", "Remarks", "Status"];
        }
    }

}