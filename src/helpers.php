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
