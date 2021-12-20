<?php

namespace App\Exports;

use App\Models\Motor;
use App\Models\Warranty;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MotorExport implements FromCollection, WithHeadings
{

    public function __construct($type, $fromDate, $toDate, $status)
    {
        $this->type = $type;
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
        $this->status = $status;
    }

    public function collection()
    {
        $collection = [];
            $arr = Motor::with(['vehicle', 'dealer', 'driver', 'insurer', 'proposer']);

            if ($this->type != '') {
                if ($this->fromDate != '') {
                    $arr = $arr->where('motors.' . $this->type, '>=', $this->fromDate);
                }
                if ($this->toDate != '') {
                    $arr = $arr->where('motors.' . $this->type, '<=', $this->toDate);
                }
            }
            if ($this->status != '') {
                $arr = $arr->where('motors.status', 'like', '%'.$this->status.'%');
            }
            $arr = $arr->orderBy('motors.id', 'desc')->get()->toArray();

            foreach ($arr as $motor) {
                $data = [
                    "Date" => $motor['created_at'],
                    "Type" => $motor['driver'] ? $motor['driver']['occupations'] : "",
                    "Dealer" => $motor['dealer'] ? $motor['dealer']['name'] : "",
                    "Name" => $motor['driver'] ? $motor['driver']['name'] : "",
                    "Vehicle No" => $motor['vehicle'] ? $motor['vehicle']['registration_no'] : "",
                    "Policy No" => $motor['policy_no'],
                    "Policy Expiry Date" => $motor['format_expiry_date'],
                    "Contact No" => $motor['driver'] ? $motor['driver']['contact_number'] : "",
                    "Email Address" => $motor['driver'] ? $motor['driver']['email'] : "",
                    "Insurer" => $motor['insurer'] ? $motor['insurer']['name'] : "",
                    "Current NCD" => $motor['driver'] ? $motor['driver']['ncd'] : "",
                    "Type of Cover & Plan" => $motor['vehicle'] ? $motor['vehicle']['chassis_no'] : "",
                    "Payment Date" => "-",
                    "Premium" => $motor['price'],
                    "GST" => number_format($motor['price'] * 0.07, 2, '.', ''),
                    "Total (incl GST)" => number_format($motor['price'] + ($motor['price'] * 0.07), 2, '.', ''),
                    "Date of Birth" => $motor['driver'] ? $motor['driver']['dob'] : "",
                    "Vehicle Type" => $motor['vehicle'] ? $motor['vehicle']['type'] : "",
                    "First Registration Date" => $motor['format_start_date'],
                    'Open Market Value' => "",
                    "Propellant" => "",
                    "Road Tax Expiry Date" => $motor['vehicle'] ? $motor['vehicle']['tax_expiry_date'] : ""
                ];
                $collection[] = $data;
            }

        return collect($collection)->map(function ($row) {
            return (object) $row;
        });
    }

    public function headings(): array
    {
        return [
            'Date', 
            'Type',
            'Dealer',
            'Name',
            'Vehicle No',
            'Policy No',
            'Policy Expiry Date',
            'Contact No.',
            'Email Address',
            'Insurer',
            'Current NCD',
            'Type of Cover & Plan',
            'Payment Date',
            'Premium',
            'GST',
            'Total (incl GST)',
            'Date of Birth*',
            'Vehicle Type',
            'First Registration Date',
            'Open Market Value',
            'Propellant',
            'Road Tax Expiry Date'
        ];
    }
}
