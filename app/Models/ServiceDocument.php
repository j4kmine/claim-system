<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceDocument extends Model
{
    use HasFactory;

    protected $table = 'service_documents';

    protected $fillable = [
        'service_id', 'name', 'url', 'type'
    ];
}
