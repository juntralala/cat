<?php

namespace App\Casts;

use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use RuntimeException;

class CarbonToEpochSecondCaster implements CastsAttributes
{
    /**
     * @param string|number|Carbon $value
     * @param array $attributes
     * @return Carbon
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): Carbon
    {
        return Date::createFromTimestamp($value);
    }
    
    public function set(Model $model, string $key, mixed $value, array $attributes): int
    {
        if (is_numeric($value)) {
            return $value;
        }
        if ($value instanceof Carbon) {
            return $value->getTimestamp();
        }
        
        throw new RuntimeException("The type of value you pass does't supported");
    }
}
