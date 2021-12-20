<?php

namespace App\Exports;

use App\Models\WarrantyPrice;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Models\Company;

class PricesExport implements FromCollection, WithHeadings
{

    public function collection()
    {
        $collection = [];
        $arr = WarrantyPrice::get()->toArray();
        $temp_insurers = [];
        $insurers = Company::where('type', 'insurer')->get();
        foreach($insurers as $insurer){
            $temp_insurers[$insurer->id] = $insurer->code;
        }

        foreach($arr as $price){
            $collection[] = [
                'make' => $price['make'],
                'model' => $price['model'],
                'capacity' => $price['capacity'],
                'new_car' => $price['type'] == 'new' ? 'TRUE' : 'FALSE',
                'fuel_type' => $price['fuel'],
                'warranty_duration' => $price['warranty_duration'],
                'price' => $price['price'],
                'max_claim' => $price['max_claim'],
                'mileage_coverage' => $price['mileage_coverage'],
                'package' => $price['package'],
                'make_category' => $price['category'],
                'active' => $price['status'] == 'active' ? 'TRUE' : 'FALSE',
                'insurer' => $temp_insurers[$price['insurer_id']],
            ];
        }
    
        return collect($collection)->map(function ($row) {
            return (object) $row;
        });
    }

    public function headings() :array
    {
        return ["make", "model", "capacity", "new_car", "fuel_type", "warranty_duration", "price", "max_claim", "mileage_coverage", "package", "make_category", "active", "insurer"];    
    }

}