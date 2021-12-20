<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $table = 'packages';

    protected $fillable = [
        'name', 'duration', 'type', 'mileage_coverage'
    ];

    protected $appends = ['package_period', 'format_mileage_coverage'];

    public function getPackagePeriodAttribute(){
        if($this->duration > 1){
            return (float) $this->duration . '-years';
        } elseif ($this->duration == 1){
            return (float) $this->duration . '-year';
        } else {
            return (float) $this->duration * 12 . '-months';
        }
    }
    public function getFormatMileageCoverageAttribute(){
        if($this->mileage_coverage != null){
            return number_format($this->mileage_coverage) . " KM";
        } else {
            return "";
        }
    }
}
