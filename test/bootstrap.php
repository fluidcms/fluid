<?php

date_default_timezone_set(@date_default_timezone_get());
error_reporting(E_ALL);
ini_set('display_errors', 'On');

$vendor = realpath(__DIR__ . '/../vendor');

if (file_exists($vendor . "/autoload.php")) {
    require $vendor . "/autoload.php";
} else {
    $vendor = realpath(__DIR__ . '/../../../');
    if (file_exists($vendor . "/autoload.php")) {
        require $vendor . "/autoload.php";
    } else {
        throw new Exception("Unable to load dependencies");
    }
}

Fluid\Autoloader::register();

spl_autoload_register(function ($class) {
    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    $class = preg_replace('/^Fluid\/Tests/', 'Fluid/Tests/_includes', $class);
    if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . $class . '.php')) {
        require_once __DIR__ . DIRECTORY_SEPARATOR . $class . '.php';
    }
});