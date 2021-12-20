<?php

namespace App\Utilities;

use Illuminate\Support\Facades\Log;
use Throwable;

class QueryFilterBuilder
{
    /**
     * The query is a builder
     * we continue the query
     */
    protected $query;

    /**
     * Class Filter is the parameters
     * where consists two possibilities:
     * 1. ?draw_status=close followed by a value
     * 2. ?asc followed by nothing
     */
    protected $classFilters;

    /**
     * The namespace is where the class filter will look up
     * By default, it should be on App\Utilities\SurveyFilter folder or namespace
     */
    protected $namespace;

    public function __construct($query, $classFilters, $namespace = null)
    {
        $this->query = $query;
        $this->classFilters = $classFilters;
        $this->namespace = $namespace ?? 'App\Utilities\JackpotFilter';
    }

    public function apply()
    {
        foreach ($this->classFilters as $name => $value) {
            $normailizedName = ucfirst($name);
            $class = $this->namespace . "\\{$normailizedName}";

            try {
                if (!class_exists($class)) {
                    Log::error($class . ' not exist');
                    continue;
                }
            } catch (Throwable $e) {
                continue;
            }

            if (strlen($value)) {
                (new $class($this->query))->handle($value);
            } else {
                (new $class($this->query))->handle();
            }
        }

        return $this->query;
    }
}
