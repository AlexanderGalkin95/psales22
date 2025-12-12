<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class PeriodJsonToArray implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get($model, string $key, mixed $value, array $attributes)
    {
        $value = json_decode($value, true) ?? [];
        sort($value);
        return $value;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     * @return false|string
     */
    public function set($model, string $key, $value, array $attributes): bool|string
    {
        return json_encode($value);
    }
}
