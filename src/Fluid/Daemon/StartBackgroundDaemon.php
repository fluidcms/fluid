<?php
namespace Fluid\Daemon;

use Fluid\Config;
use Fluid\Logger;
use Fluid\ErrorHandler;

$vendor = realpath(__DIR__ . "/../../../../../");

if (is_dir($vendor) && is_file($vendor . "/autoload.php")) {
    require_once $vendor . "/autoload.php";

    if (!isset($argv) && isset($_SERVER['argv'])) {
        $argv = $_SERVER['argv'];
    }

    if (isset($argv) && isset($argv[1]) && isset($argv[2])) {
        $config = new Config();
        $config->unserialize(base64_decode($argv[1]));

        $logger = null;
        if ($config->getDebug()) {
            require_once __DIR__ . "/../Logger.php";
            require_once __DIR__ . "/../ErrorHandler.php";

            $logger = new Logger($config);

            set_error_handler(['Fluid\\ErrorHandler', 'error']);
            register_shutdown_function(['Fluid\\ErrorHandler', 'shutdown']);

            ErrorHandler::$logger = $logger;
        }

        if (isset($argv[3])) {
            $timezone = base64_decode($argv[3]);
            if (@date_default_timezone_get() !== $timezone) {
                date_default_timezone_set($timezone);
            }
        }

        $instanceId = base64_decode($argv[2]);
        $daemon = new Daemon($config, $logger, null, $instanceId);

        if (is_file($daemon->getPidFilePath()) && is_writable($daemon->getPidFilePath())) {
            $daemon->stop();
        }

        if (is_file($daemon->getLockFilePath()) && is_writable($daemon->getLockFilePath())) {
            unlink($daemon->getLockFilePath());
        }

        $pid = pcntl_fork();
        $daemon->setPid($pid);

        if (isset($pid) && $pid !== -1 && !$pid) {
            $parantPid = posix_getppid();
            if ($parantPid) {
                posix_kill(posix_getppid(), SIGUSR2);
                return null;
            }
        }

        if (!isset($parantPid) || !$parantPid) {
            $daemon->run();
        }
    }
} else {
    trigger_error("Package Fluid is not installed properly");
}