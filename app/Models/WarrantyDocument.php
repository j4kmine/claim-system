<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Storage;
use Carbon\Carbon;

class WarrantyDocument extends Model
{
    protected $fillable = ['warranty_id','name','url','type'];

    protected $appends = ['view'];

    public function getViewAttribute(){
        return Storage::disk('s3')->temporaryUrl(
            'warranty/'.$this->type.'/'.basename($this->url), Carbon::now()->addMinutes(30)
        );
    }
}
