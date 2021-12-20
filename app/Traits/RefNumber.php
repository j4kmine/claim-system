<?php

namespace App\Traits;

use Illuminate\Support\Str;

use Illuminate\Database\Eloquent\ModelNotFoundException;


trait RefNumber
{
    /**
     * Boot function from Laravel
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->ref_no) {
                $model->ref_no = self::generateUniqueRef();
            }
        });
    }

    private static function generateUniqueRef()
    {
        $exists = true;
        $ref = '';
        while ($exists) {
            $ref = 'RFQ-' . mt_rand(1000000000, 9999999999);
            $exists = self::where('ref_no', $ref)->exists();
        }
        return $ref;
    }

    public function scopeRef($query, $refNo, $first = true)
    {
        if (!is_string($refNo)) {
            throw (new ModelNotFoundException)->setModel(get_class($this));
        }

        $search = $query->where('ref_no', $refNo);

        return $first ? $search->first() : $search;
    }
}
