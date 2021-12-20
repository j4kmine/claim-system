<?php

namespace App\Models;

use Storage;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccidentReportDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'accident_report_documents';

    protected $guarded = [];

    protected $appends = ['view'];

    const TYPES = [
        'accident_report', 'inspection_report'
    ];

    public function getViewAttribute(){
        return Storage::disk('s3')->temporaryUrl(
            'accident/'.$this->type.'/reports/'.basename($this->url), Carbon::now()->addMinutes(30)
        );
    }
}
