<?php

namespace App\Exports;

use App\Models\Warranty;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class WarrantyExport implements FromCollection, WithHeadings
{

    public function __construct($vehicleType, $type, $fromDate, $toDate, $status)
    {
        $this->type = $type;
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
        $this->status = $status;
        $this->vehicleType = $vehicleType;
    }

    public function collection()
    {
        $collection = [];
            $arr = Warranty::with(['vehicle.motor', 'dealer', 'insurer', 'proposer']);
            if ($this->vehicleType != ''){
                $arr = $arr->whereHas('vehicle', function($query){
                    $query->where('type', $this->vehicleType);
                });
            }

            if ($this->type != '') {
                if ($this->fromDate != '') {
                    $arr = $arr->where('warranties.' . $this->type, '>=', $this->fromDate);
                }
                if ($this->toDate != '') {
                    $arr = $arr->where('warranties.' . $this->type, '<=', $this->toDate);
                }
            }
            if ($this->status != '') {
                $arr = $arr->where('warranties.status', $this->status);
            }
            $arr = $arr->orderBy('warranties.id', 'desc')->get()->toArray();

            foreach ($arr as $warranty) {
                $data = [
                    "CI No" => $warranty['ci_no'],
                    "Policy No" => $warranty['vehicle'] ? $warranty['vehicle']['motor'] ? $warranty['vehicle']['motor']['policy_no'] : "" : "",
                    'Issuer' => $warranty['insurer'] ? $warranty['insurer']['name'] : "",
                    'Account No' => $warranty['insurer'] ? $warranty['insurer']['code'] : "",
                    'Endorsement applicable' => 'Not Applicable',
                    "Issue Date" => $warranty['created_at'],
                    "Warranty Effective Date*" => $warranty['end_date'],
                    "Warranty Expiry Date*" => $warranty['end_date'],
                    "Owner's Name" => $warranty['proposer'] ? $warranty['proposer']['name'] : "",
                    "Vehicle Registration Number*" => $warranty['vehicle'] ? $warranty['vehicle']['registration_no'] : "",
                    "Make" => $warranty['vehicle'] ? $warranty['vehicle']['make'] : "",
                    "Model" => $warranty['vehicle'] ? $warranty['vehicle']['model'] : "",
                    "Mileage Cover" => $warranty['mileage_coverage'],
                    "Maximum claim limit per year" => $warranty['max_claim'],
                    "Engine Number" => $warranty['vehicle'] ? $warranty['vehicle']['engine_no'] : "",
                    "Chassis Number" => $warranty['vehicle'] ? $warranty['vehicle']['chassis_no'] : "",
                    "Vehicle Capacity" => $warranty['vehicle'] ? $warranty['vehicle']['capacity'] : "",
                    "Year of Manufacture" => $warranty['vehicle'] ? $warranty['vehicle']['manufacture_year'] : "",
                    "Vehicle Registration Date" => $warranty['vehicle'] ? $warranty['vehicle']['format_registration_date'] : "",
                    "Numbers of Warranty period*" => $warranty['warranty_duration'],
                    "Warranty commencement mileage" => $warranty['mileage'],
                    "Coverage Premium" => $warranty['format_premium_per_year'],
                    "Dealer's Name" => $warranty['dealer'] ? $warranty['dealer']['name'] : "",
                    "Dealer's Address" => $warranty['dealer'] ? $warranty['dealer']['address'] : ""
                ];
                if ($this->vehicleType != 'preowned' || $this->vehicleType==''){
                    unset($data['Policy No']);
                    unset($data['Issuer']);
                    unset($data['Account No']);
                    unset($data['Endorsement applicable']);
                }
                $collection[] = $data;
            }

        return collect($collection)->map(function ($row) {
            return (object) $row;
        });
    }

    public function headings(): array
    {
        if ($this->vehicleType == 'new' || $this->vehicleType == ''){
            return [
                "CI No",
                "Issue Date",
                "Warranty Effective Date*",
                "Warranty Expiry Date*",
                "Owner's Name",
                "Vehicle Registration Number*",
                "Make",
                "Model", 
                "Mileage Cover", 
                "Maximum claim limit per year", 
                "Engine Number", 
                "Chassis Number", 
                "Vehicle Capacity", 
                "Year of Manufacture", 
                "Vehicle Registration Date", 
                "Numbers of Warranty period*", 
                "Warranty commencement mileage", 
                "Coverage Premium", 
                "Dealer's Name", 
                "Dealer's Address", 
            ];
        }else{
            return [
                "CI No",
                'Policy No',
                'Issuer',
                'Account No',
                'Endorsement applicable',
                "Issue Date",
                "Warranty Effective Date*",
                "Warranty Expiry Date*",
                "Owner's Name",
                "Vehicle Registration Number*",
                "Make",
                "Model", 
                "Mileage Cover", 
                "Maximum claim limit per year", 
                "Engine Number", 
                "Chassis Number", 
                "Vehicle Capacity", 
                "Year of Manufacture", 
                "Vehicle Registration Date", 
                "Numbers of Warranty period*", 
                "Warranty commencement mileage", 
                "Coverage Premium", 
                "Dealer's Name", 
                "Dealer's Address", 
            ];
        }
    }
}
