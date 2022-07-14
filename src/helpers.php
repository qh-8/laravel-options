<?php

if (! function_exists('option')) {
    /**
     * Get / set the specified option value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @param  array|string|null  $key
     * @param  mixed  $default
     * @return mixed|\Qh\LaravelOptions\Repository
     */
    function option($key = null, $default = null)
    {
        if (is_null($key)) {
            return app('options');
        }

        if (is_array($key)) {
            return app('options')->set($key);
        }

        return app('options')->get($key, $default);
    }
}
