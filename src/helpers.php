<?php

use Illuminate\Support\Str;

function generateRandomString($length = 6){
    $randomString = Str::random($length);
    return $randomString;
}
