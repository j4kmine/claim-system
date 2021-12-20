<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccidentReport extends Model
{
    use HasFactory;

    protected $table = 'accident_reports';

    protected $guarded = [];

    public function documents()
    {
        return $this->hasMany(AccidentReportDocument::class);
    }
}
