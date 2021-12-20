<?php

namespace App\Models;

use Storage;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServicingReportInvoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'servicing_report_invoices';

    protected $guarded = [];

    protected $appends = ['view'];

    public function getViewAttribute(){
        return Storage::disk('s3')->temporaryUrl(
            'servicing/invoice/invoices/'.basename($this->url), Carbon::now()->addMinutes(30)
        );
    }
}
