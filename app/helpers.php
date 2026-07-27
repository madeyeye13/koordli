<?php

use App\Helpers\TerminologyHelper;

if (!function_exists('term')) {
    function term(string $key, string $default): string
    {
        return TerminologyHelper::term($key, $default);
    }
}

if (!function_exists('term_title')) {
    function term_title(string $key, string $default): string
    {
        return TerminologyHelper::termTitle($key, $default);
    }
}