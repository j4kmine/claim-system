<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Storage;
use Carbon\Carbon;

class ReportDocument extends Model
{
    use HasFactory;

    protected $table = 'report_documents';

    protected $guarded = [];

    const TYPES = [
        'accident-scene',
        'own-vehicle',
        'other-property',
        'insurer-invoice',
        'license-plate',
        'close-range-damage',
        'long-range-damage',
        'other.driving-license',
        'other.license-plate',
        'other.close-range-damage',
        'other.long-range-damage'
    ];

    protected $appends = ['view'];

    public function getViewAttribute(){
        return Storage::disk('s3')->temporaryUrl(
            'accident/'.$this->type.'/'.basename($this->url), Carbon::now()->addMinutes(30)
        );
    }
}
