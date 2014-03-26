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

        if (isset($argv[3])) {
            $timezone = base64_decode($argv[3]);
            if (@date_default_timezone_get() !== $timezone) {
                date_default_timezone_set($timezone);
            }
        }

        $logger = null;
        if ($config->getDebug()) {
            require_once __DIR__ . "/../Logger.php";
            require_once __DIR__ . "/../ErrorHandler.php";

            $logger = new Logger($config);
        }

        $instanceId = base64_decode($argv[2]);
        $daemon = new Daemon($config, null, $logger, null, null, $instanceId);

        if (is_file($daemon->getPidFilePath()) && is_writable($daemon->getPidFilePath())) {
            $daemon->stop();
        }

        if (is_file($daemon->getLockFilePath()) && is_writable($daemon->getLockFilePath())) {
            unlink($daemon->getLockFilePath());
        }

        $pid = pcntl_fork();

        if ($pid !== -1 && !$pid) {
            if ($pid !== -1 && !$pid) {
                $perentPid = posix_getppid();
                if ($perentPid) {
                    posix_kill(posix_getppid(), SIGUSR2);
                }
            }

            if ($config->getDebug()) {
                $logger = new Logger($config);

                set_error_handler(['Fluid\\ErrorHandler', 'error']);
                register_shutdown_function(['Fluid\\ErrorHandler', 'shutdown']);

                ErrorHandler::$logger = $logger;
                $daemon->setLogger($logger);
            }

            $daemon->run();
        } elseif ($pid) {
            $wait = true;

            pcntl_signal(SIGUSR2, function () use (&$wait) {
                $wait = false;
            });

            while ($wait) {
                pcntl_waitpid($pid, $status, WNOHANG);
                pcntl_signal_dispatch();
            }

            $daemon->setPid($pid);
        }
    }
} else {
    trigger_error("Package Fluid is not installed properly");
}