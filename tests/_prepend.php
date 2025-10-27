<?php
$basepath = dirname(__FILE__, 2);
include_once $basepath . '/vendor/autoload.php';

session_start();


function vd(mixed ...$args): void
{
    foreach ($args as $arg) {
        error_log(print_r($arg, true));
    }
}

