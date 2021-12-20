<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $table = 'settings';

    protected $fillable = [
        'key', 'name', 'type'
    ];

    /**
     * Setting Types
     */
    public $TYPES = [
        'global.setting', 'customer.setting', 'user.setting'
    ];

    public function scopeCustomerSetting($query)
    {
        return $query->orWhere('type', 'customer.setting');
    }

    public function scopeGlobalSetting($query)
    {
        return $query->orWhere('type', 'global.setting');
    }

    public function scopeUserSetting($query)
    {
        return $query->orWhere('type', 'user.setting');
    }
}
