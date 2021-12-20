<?php

namespace App\Traits;

trait AttributeModifier
{
    /**
     * Modify attribute keys,
     * for easily updating/inserting data to DB
     * 
     * @param array $attributes
     * @param array $keys
     * @return array
     */
    public function modifyKeys(array $attributes, array $keys): array
    {
        $output = [];

        foreach ($attributes as $key => $value) {
            if (array_key_exists($key, $keys))
                $output = array_merge($output, [
                    $keys[$key] => $value
                ]);
            else
                $output = array_merge($output, [$key => $value]);
        }

        return $output;
    }
}
