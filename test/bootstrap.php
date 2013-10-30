<?php

date_default_timezone_set("America/Montreal");
error_reporting(E_ALL);
ini_set('display_errors', 'On');

$loader = require __DIR__ . '/../../../autoload.php';
$loader->register();

require_once __DIR__ . '/helper.php';

Fluid\Fluid::setConfig('languages', array('en-US', 'de-DE'));
Fluid\Fluid::setConfig('storage', __DIR__ . '/Fluid/Tests/_files/storage/');
Fluid\Fluid::setConfig('templates', __DIR__ . '/Fluid/Tests/_files/templates');
Fluid\Fluid::setConfig('layouts', 'layouts');
Fluid\Fluid::setConfig('components', 'components');

