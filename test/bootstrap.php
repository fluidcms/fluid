<?php

date_default_timezone_set("America/Montreal");
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

require_once __DIR__ . '/helper.php';

Fluid\Config::setAll(array(
    'languages' => array('en-US', 'de-DE'),
    'storage' => __DIR__ . '/Fluid/Tests/_files/storage/',
    'templates' => __DIR__ . '/Fluid/Tests/_files/templates',
    'layouts' => 'layouts',
    'components' => 'components'
));

