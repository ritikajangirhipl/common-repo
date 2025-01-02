<?php

use Illuminate\Support\Str;

if (!function_exists('generateRandom2FaString')) {
    function generateRandom2FaString($length = 6){
        $randomString = Str::random($length);
        return $randomString;
    }
}