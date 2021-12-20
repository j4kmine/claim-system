<?php

namespace App\Utilities\ServiceFilter;

use App\Utilities\QueryFilter;
use App\Utilities\FilterContract;

class Asc extends QueryFilter implements FilterContract
{
    public function handle($value = null): void
    {
        $this->query->orderBy($value, 'asc');
    }
}
