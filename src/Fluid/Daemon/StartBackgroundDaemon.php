<?php

namespace Fluid\Daemon;

use Fluid\Fluid;

$vendor = realpath(__DIR__ . "/../../../../../");

if (is_dir($vendor) && is_file($vendor . "/autoload.php")) {
    require_once $vendor . "/autoload.php";

    if (!isset($argv) && isset($_SERVER['argv'])) {
        $argv = $_SERVER['argv'];
    }

    if (isset($argv) && isset($argv[2])) {
        $config = unserialize(base64_decode($argv[1]));

        Fluid::init($config);

        if (isset($argv[3])) {
            $debugMode = (int)$argv[3];
            if ($debugMode !== 0) {
                require_once __DIR__ . "/../Debug/Log.php";

                set_error_handler(array('Fluid\\Debug\\ErrorHandler', 'error'));
                register_shutdown_function(array('Fluid\\Debug\\ErrorHandler', 'shutdown'));

                Fluid::debug($debugMode);
            }
        }

        if (isset($argv[4])) {
            $timeZone = base64_decode($argv[4]);
            if (@date_default_timezone_get() !== $timeZone) {
                date_default_timezone_set($timeZone);
            }
        }

        $class = "\\Fluid\\Daemon\\Daemon";
        /** @var Daemon $daemon */
        $daemon = new $class($argv[2]);
        $daemon->run();
    }
} else {
    trigger_error("Package Fluid is not installed properly");
}
