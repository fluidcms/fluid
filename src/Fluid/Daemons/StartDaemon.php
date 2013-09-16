<?php

namespace Fluid\Daemons;
use Fluid\Fluid;

$vendor = realpath(__DIR__ . "/../../../../../");

if (is_dir($vendor) && is_file($vendor."/autoload.php")) {
    require_once $vendor."/autoload.php";

    if (isset($argv)) {
        $config = unserialize(base64_decode($argv[2]));

        Fluid::init($config);

        $class = "\\Fluid\\Daemons\\" . $argv[1];
        $daemon = new $class();
        $daemon->run();
    }
} else {
    trigger_error("Package Fluid is not installed properly");
}
