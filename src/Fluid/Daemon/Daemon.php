<?php

namespace Fluid\Daemon;

use Fluid;
use React;
use Ratchet;
use Closure;
use Fluid\Socket\Server;
use Fluid\Socket\Server\WebSocket;
use Fluid\Socket\Server\Message;
use Fluid\Debug\Log;
use Fluid\Config;

class Daemon implements DaemonInterface
{
    const LOCK_FILE = '.server.lock';

    /** @var callable $uptimeCallback */
    private $uptimeCallback;

    /** @var string $instanceId */
    private $instanceId;

    /** @var resource $lock */
    private $lock;

    /** @var int $timeStart */
    private $timeStart;

    /**
     * Init
     *
     * @param Closure $uptimeCallback
     * @param string $instanceId
     */
    public function __construct(Closure $uptimeCallback = null, $instanceId = "")
    {
        $this->timeStart = time();
        $this->uptimeCallback = $uptimeCallback;
        $this->instanceId = $instanceId;
    }

    /**
     * Execute the up time callback
     *
     * @return  void
     */
    protected function uptimeCallback()
    {
        if (isset($this->uptimeCallback) && is_callable($this->uptimeCallback)) {
            call_user_func($this->uptimeCallback);
        }
    }

    /**
     * Check if daemon is running
     *
     * @return  bool
     */
    public static function isRunning()
    {
        $dir = Config::get('storage');
        $handler = fopen($dir . '/' . self::LOCK_FILE, "w+");

        if (!flock($handler, LOCK_SH | LOCK_NB)) {
            fclose($handler);
            return true;
        }

        fclose($handler);
        return false;
    }

    /**
     * Start daemon in background
     *
     * @return  bool
     */
    public static function runBackground()
    {
        $instanceId = uniqid();
        $debugMode = Fluid\Fluid::getDebugMode();
        $timeZone = date_default_timezone_get();

        shell_exec(
            "php -q " . __DIR__ . "/StartBackgroundDaemon.php " .
            base64_encode(serialize(Config::getAll())) . " " .
            " {$instanceId} {$debugMode} " .
            base64_encode($timeZone) .
            " > /dev/null &"
        );

        $dir = Config::get('storage');
        $i = 0;
        while ($i < 100) {
            $fileContent = file_get_contents($dir . '/' . self::LOCK_FILE);
            if ($fileContent === $instanceId) {
                return true;
            }
            $i++;
            usleep(100000);
        }
        return false;
    }

    /**
     * Create and lock the lock file
     *
     * @return  mixed
     */
    private function lock()
    {
        $dir = Config::get('storage');

        if (!is_dir($dir)) {
            mkdir($dir);
        }

        $this->lock = fopen($dir . '/' . self::LOCK_FILE, "w+");

        if (flock($this->lock, LOCK_EX | LOCK_NB)) {
            return true;
        }

        return false;
    }

    /**
     * Release the lock file
     *
     * @return  mixed
     */
    private function release()
    {
        $dir = Config::get('storage');

        file_put_contents($dir . '/' . self::LOCK_FILE, "");

        flock($this->lock, LOCK_UN);
        fclose($this->lock);
    }

    /*
     * Run the web socket server
     *
     * @return  void
     */
    public function run()
    {
        if (!$this->lock()) {
            return;
        }

        $port = Config::get('websocket');
        Log::add('Starting socket on port ' . $port);

        $root = $this;

        $server = new Server();

        $loop = $server->getLoop();

        // Create WebSocket Server
        $server->add(new WebSocket(), WebSocket::URI);
        // Create Message Server
        //$server->add(new Message(), Message::URI);

        $server->create();

        // Stop server if inactive for 30 seconds
        $loop->addPeriodicTimer(30, function () use ($root) {
            // TODO if inactive, stop!
        });

        // Execute uptime callback every 10 seconds
        $loop->addPeriodicTimer(10, function () use ($root) {
            $root->uptimeCallback();
        });

        file_put_contents(Config::get('storage') . '/' . self::LOCK_FILE, $this->instanceId);
        $server->run();

        $this->release();
    }
}
