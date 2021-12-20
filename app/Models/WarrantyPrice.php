<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use JamesDordoy\LaravelVueDatatable\Traits\LaravelVueDatatableTrait;

class WarrantyPrice extends Model
{

    use LaravelVueDatatableTrait, HasFactory;

    protected $dataTableColumns = [
        'id' => [
            'searchable' => false,
            'orderable' => true
        ],
        'make' => [
            'searchable' => false,
            'orderable' => true
        ],
        'model' => [
            'searchable' => true,
            'orderable' => true
        ],
        'capacity' => [
            'searchable' => true,
            'orderable' => true
        ],
        'category' => [
            'searchable' => true,
            'orderable' => true
        ],
        'type' => [
            'searchable' => true,
            'orderable' => true
        ],
        'fuel' => [
            'searchable' => false,
            'orderable' => true
        ],
        'price' => [
            'searchable' => true,
            'orderable' => true
        ],
        'max_claim' => [
            'searchable' => true,
            'orderable' => true
        ],
        'mileage_coverage' => [
            'searchable' => true,
            'orderable' => true
        ],
        'warranty_duration' => [
            'searchable' => true,
            'orderable' => true
        ],
        'package' => [
            'searchable' => true,
            'orderable' => true
        ],
        'status' => [
            'searchable' => true,
            'orderable' => true
        ]
    ];
    protected $fillable = ['insurer_id', 'make', 'model', 'category', 'capacity', 'type', 'fuel', 'price', 'max_claim', 'mileage_coverage', 'package', 'warranty_duration', 'status'];
    protected $appends = ['warranty_period', 'format_price', 'format_max_claim', 'format_mileage_coverage', 'format_premium_per_year'];

    public function getWarrantyPeriodAttribute(){
        if($this->warranty_duration > 1){
            return (float) $this->warranty_duration . '-years';
        } elseif ($this->warranty_duration == 1){
            return (float) $this->warranty_duration . '-year';
        } else {
            return (float) $this->warranty_duration * 12 . '-months';
        }
    }

    public function getFormatMileageCoverageAttribute(){
        if($this->mileage_coverage != null){
            return number_format($this->mileage_coverage)." KM";
        } else {
            return null;
        }
    }

    public function getFormatPremiumPerYearAttribute(){
        if($this->price != null && $this->warranty_duration != null){
            return "$".number_format($this->price / $this->warranty_duration, 2);
        } else {
            return null;
        }
    }

    public function getFormatPriceAttribute(){
        if($this->price != null){
            return "$".number_format($this->price, 2, ".", "");
        } else {
            return null;
        }
    }

    public function getFormatMaxClaimAttribute(){
        if($this->max_claim != null){
            return "$".number_format($this->max_claim);
        } else {
            return null;
        }
    }

    public function insurer()
    {
        return $this->belongsTo(Company::class, 'insurer_id');
    }

}