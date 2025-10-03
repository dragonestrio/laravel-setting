<?php

if (!function_exists('array_keys_exists')) {
    /**
     * Easily check if multiple array keys exist.
     *
     * @param array $keys
     * @param array $arr
     *
     * @return boolean
     */
    function array_keys_exists(array $keys, array $arr)
    {
        return !array_diff_key(array_flip($keys), $arr);
    }
}

if (!function_exists('setting')) {
    /**
     * Get / set the specified setting value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @param array|string $key
     * @param mixed $default
     *
     * @return mixed
     */
    function setting($key = null, $default = null): mixed
    {
        $setting = app('setting');

        if (is_null($key)) {
            return $setting;
        }

        if (is_array($key)) {
            $setting->set($key);

            return $setting;
        }

        $setting = $setting->get($key, $default);

        $keyExplode = explode('.', (string) $key);
        $keyGroup = current($keyExplode);
        $keyLast = end($keyExplode);
        $keyHasSub = count($keyExplode) > 1;
        $keyCast = @config('setting.casting_keys', [])[$keyGroup][$keyLast];

        if (($keyGroup == $keyLast) && (! $keyHasSub)) {
            $keyCast = @config('setting.casting_keys', [])[$keyGroup];
        }

        $keyHasCast = ! empty($keyCast);

        if ($keyHasCast && $keyHasSub) {
            $setting = settingCasting($setting, $keyCast);
        }

        if ($keyHasCast && (! $keyHasSub)) {
            $castedKeys = array_intersect_key($setting, $keyCast);

            foreach ($castedKeys as $key => $value) {
                $setting[$key] = settingCasting($value, $keyCast[$key]);
            }
        }

        return $setting;
    }
}

if (!function_exists('settingCasting')) {
    /**
     * Cast the specified setting value.
     *
     * @param mixed $value
     * @param string|null $castType
     *
     * @return mixed
     */
    function settingCasting(mixed $value, ?string $castType): mixed
    {
        if (is_null($castType)) {
            return $value;
        }

        switch ($castType) {
            case 'int':
            case 'integer':
                return (int) $value;

            case 'real':
            case 'float':
            case 'double':
            case 'decimal':
                return (float) $value;

            case 'string':
                return (string) $value;

            case 'bool':
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOL);

            case 'array':
                if (is_array($value)) {
                    return $value;
                }
                if (is_string($value)) {
                    $decoded = json_decode($value, true);

                    return $decoded ?? ($value !== '' ? [$value] : []);
                }

                return (array) $value;

            case 'json':
                return is_string($value) ? json_decode($value, true) : $value;

            case 'collection':
                return collect($value);

            case 'datetime':
                return \Illuminate\Support\Carbon::parse($value);

            default:
                return $value;
        }
    }
}
