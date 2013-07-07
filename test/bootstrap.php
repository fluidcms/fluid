<?php

date_default_timezone_set("America/Montreal");
error_reporting(E_ALL);
ini_set('display_errors', 'On');

require_once __DIR__ . '/../src/Fluid/Autoloader.php';
require_once __DIR__ . '/helper.php';
Fluid\Autoloader::register();

Fluid\Fluid::setConfig('languages', array('en-US', 'de-DE'));
Fluid\Fluid::setConfig('storage', __DIR__ . '/Fluid/Tests/Fixtures/storage/');

require_once __DIR__ . '/../../../twig/twig/lib/Twig/Autoloader.php';
Twig_autoloader::register();
