<?php

namespace App\Imports;

use App\Models\WarrantyPrice;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use App\Models\Company;

class PricesImport implements ToModel, WithBatchInserts, WithChunkReading
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    private $insurers;

    public function __construct() 
    {
        $insurers = Company::where('type', 'insurer')->get();
        foreach($insurers as $insurer){
            $this->insurers[$insurer->code] = $insurer->id;
        }
    }

    public function model(array $row)
    {
        // If is heading, skip
        if($row[0] == 'make'){
            return;
        }
        
        return new WarrantyPrice([
            //
            'make' => $row[0],
            'model' => $row[1],
            'capacity' => $row[2],
            'type' => $row[3] == 'TRUE' ? 'new' : 'preowned',
            'fuel' => $row[4],
            'warranty_duration' => $row[5],
            'price' => $row[6],
            'max_claim' => $row[7],
            'mileage_coverage' => $row[8],
            'package' => $row[9],
            'category' => $row[10],
            'status' => $row[11] == 'TRUE' ? 'active' : 'inactive',
            'insurer_id' => $this->insurers[$row[12]]
        ]);
    }

    public function batchSize(): int
    {
        return 1000;
    }
    
    public function chunkSize(): int
    {
        return 1000;
    }
}
