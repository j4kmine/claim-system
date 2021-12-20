<?php

namespace App\Utilities\ServiceFilter;

use App\Utilities\QueryFilter;
use App\Utilities\FilterContract;

class Status extends QueryFilter implements FilterContract
{
    public function handle($value = null): void
    {
        $this->query->whereStatus($value);
    }
}
