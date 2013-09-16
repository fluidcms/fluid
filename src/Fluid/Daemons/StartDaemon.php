<?php

namespace Fluid\Daemons;
use Fluid\Fluid;

$vendor = realpath(__DIR__ . "/../../../../../");

if (is_dir($vendor) && is_file($vendor."/autoload.php")) {
    require_once $vendor."/autoload.php";

    if (isset($argv) && isset($argv[3])) {
        $config = unserialize(base64_decode($argv[2]));

        Fluid::init($config);

        if (isset($argv[4])) {
            $debugMode = (int)$argv[4];
            if ($debugMode !== 0) {
                Fluid::debug($debugMode);
            }
        }

        if (isset($argv[5])) {
            $timeZone = base64_decode($argv[5]);
            if (date_default_timezone_get() !== $timeZone) {
                date_default_timezone_set($timeZone);
            }
        }

        $class = "\\Fluid\\Daemons\\" . $argv[1];
        $daemon = new $class($argv[3]);
        $daemon->run();
    }
} else {
    trigger_error("Package Fluid is not installed properly");
}
