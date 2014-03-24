<?php
namespace Fluid\Daemon;

use Fluid\Config;

$vendor = realpath(__DIR__ . "/../../../../../");

if (is_dir($vendor) && is_file($vendor . "/autoload.php")) {
    require_once $vendor . "/autoload.php";

    if (!isset($argv) && isset($_SERVER['argv'])) {
        $argv = $_SERVER['argv'];
    }

    if (isset($argv) && isset($argv[2])) {
        $config = new Config();
        $config->unserialize(base64_decode($argv[1]));

        if (isset($argv[3])) {
            $debugMode = (int)$argv[3];
            if ($debugMode !== 0) {
                require_once __DIR__ . "/../Debug/Log.php";

                set_error_handler(['Fluid\\Debug\\ErrorHandler', 'error']);
                register_shutdown_function(['Fluid\\Debug\\ErrorHandler', 'shutdown']);
            }
        }

        if (isset($argv[4])) {
            $timeZone = base64_decode($argv[4]);
            if (@date_default_timezone_get() !== $timeZone) {
                date_default_timezone_set($timeZone);
            }
        }

        $instanceId = base64_decode($argv[2]);
        $daemon = new Daemon($config, null, $instanceId);

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