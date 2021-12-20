<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServicingReport extends Model
{
    use HasFactory;

    protected $table = 'servicing_reports';

    protected $guarded = [];

    public function documents()
    {
        return $this->hasMany(ServicingReportDocument::class);
    }

    public function invoices()
    {
        return $this->hasMany(ServicingReportInvoice::class);
    }
}
