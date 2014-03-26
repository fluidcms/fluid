<?php
namespace Fluid\Daemon;

use Fluid\Logger;
use Fluid\Storage;
use React;
use Ratchet;
use Fluid\Event;
use Fluid\WebsocketServer;
use Fluid\WebsocketServer\LocalWebSocketServer;
use Fluid\WebsocketServer\EventWebsocketServer;
use Fluid\ConfigInterface;
use Psr\Log\LoggerInterface;
use Fluid\StorageInterface;

class Daemon implements DaemonInterface
{
    const LOCK_FILE = '.fluid-server.lock';
    const PID_FILE = '.fluid-server.pid';

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Event
     */
    private $event;

    /**
     * @var callable
     */
    private $uptimeCallback;

    /**
     * @var string
     */
    private $instanceId;

    /**
     * @var resource
     */
    private $lock;

    /**
     * @var int
     */
    private $timeStart;

    /**
     * @var string
     */
    private $lockFilePath;

    /**
     * @var string
     */
    private $pidFilePath;

    /**
     * @param ConfigInterface $config
     * @param StorageInterface|null $storage = null
     * @param LoggerInterface|null $logger = null
     * @param Event $event
     * @param callable|null $uptimeCallback
     * @param string|null $instanceId
     */
    public function __construct(ConfigInterface $config, StorageInterface $storage = null, LoggerInterface $logger = null, Event $event = null, callable $uptimeCallback = null, $instanceId = null)
    {
        $this->setConfig($config);
        if (null !== $storage) {
            $this->setStorage($storage);
        }
        if (null !== $logger) {
            $this->setLogger($logger);
        }
        if (null !== $event) {
            $this->setEvent($event);
        }
        if (null !== $uptimeCallback) {
            $this->setUptimeCallback($uptimeCallback);
        }
        $this->timeStart = time();
        $this->setLockFilePath(sys_get_temp_dir() . DIRECTORY_SEPARATOR . self::LOCK_FILE);
        $this->setPidFilePath(sys_get_temp_dir() . DIRECTORY_SEPARATOR . self::PID_FILE);
        if (null !== $instanceId) {
            $this->instanceId = $instanceId;
        }
    }

    /**
     * Execute the uptime callback
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
     * @return bool
     */
    public function isRunning()
    {
        $handler = fopen($this->getLockFilePath(), "w+");

        if (!flock($handler, LOCK_SH | LOCK_NB)) {
            fclose($handler);
            return true;
        }

        fclose($handler);
        return false;
    }

    /**
     * @param string $pid
     */
    public function setPid($pid)
    {
        file_put_contents($this->getPidFilePath(), $pid);
    }

    /**
     * Start daemon in background
     *
     * @return bool
     */
    public function runBackground()
    {
        $instanceId = uniqid();
        $timezone = date_default_timezone_get();

        shell_exec(
            "php -q " . __DIR__ . "/StartBackgroundDaemon.php " .
            base64_encode(serialize($this->getConfig())) . " " .
            base64_encode($instanceId) . " " .
            base64_encode($timezone) . " " .
            " > /dev/null &"
        );

        $file = $this->getLockFilePath();
        $i = 0;
        while ($i < 100) {
            $fileContent = file_get_contents($file);
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
     * @return bool
     */
    private function lock()
    {
        $file = $this->getLockFilePath();

        if (!is_dir(dirname($file))) {
            mkdir(dirname($file));
        }

        file_put_contents($file, $this->instanceId);
        $this->lock = fopen($file, "w+");

        if (flock($this->lock, LOCK_EX | LOCK_NB)) {
            return true;
        }

        return false;
    }

    /**
     * Release the lock file
     */
    private function release()
    {
        file_put_contents($this->getLockFilePath(), "");
        flock($this->lock, LOCK_UN);
        fclose($this->lock);
    }

    /*
     * Run the web socket server
     */
    public function run()
    {
        $logger = $this->getLogger();

        if (!$this->lock()) {
            return;
        }

        $logger->debug('Starting socket on port ' . $this->getConfig()->getWebsocketPort());

        $root = $this;

        $server = new WebsocketServer($this->getConfig());

        $loop = $server->getReactEventLoop();

        // Create Local Websocket Server
        $localWebsocketServer = new LocalWebSocketServer($this->getConfig(), $this->getStorage(), $logger, $this->getEvent());
        $server->add($localWebsocketServer, $this->getConfig()->getAdminPath() . LocalWebSocketServer::URI);

        // Create Message Websocket Server
        $eventWebsocketServer = new EventWebsocketServer($this->getConfig(), $logger, $this->getEvent());
        $server->add($eventWebsocketServer, $this->getConfig()->getAdminPath() . EventWebsocketServer::URI);

        $server->create();

        // Stop server if inactive
        $stopServer = function() use ($loop, $localWebsocketServer, $logger) {
            if ($localWebsocketServer->isInactive()) {
                $logger->debug('Stopping socket after inactivity');
                $loop->stop();
            }
        };

        $this->getEvent()->on('websocket:connection:close', $stopServer);
        $loop->addPeriodicTimer(1, $stopServer);

        // Execute uptime callback every 10 seconds
        if (is_callable($this->uptimeCallback)) {
            $loop->addPeriodicTimer(10, function () use ($root) {
                $root->uptimeCallback();
            });
        }

        file_put_contents($this->getLockFilePath(), $this->instanceId);
        $server->run();

        $this->release();
    }

    /*
     * Stop the web socket server
     */
    public function stop()
    {
        if (file_exists($this->getPidFilePath())) {
            $pid = (int)file_get_contents($this->getPidFilePath());
        }

        if (isset($pid) && $pid) {
            posix_kill($pid, SIGUSR1);
        }

        if (file_exists($this->getPidFilePath()) && is_writable($this->getPidFilePath())) {
            unlink($this->getPidFilePath());
        }
    }

    /**
     * @param ConfigInterface $config
     * @return $this
     */
    public function setConfig(ConfigInterface $config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @return ConfigInterface
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param Event $event
     * @return $this
     */
    public function setEvent(Event $event)
    {
        $this->event = $event;
        return $this;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        if (null === $this->event) {
            $this->createEvent();
        }
        return $this->event;
    }

    /**
     * @return $this
     */
    private function createEvent()
    {
        return $this->setEvent(new Event);
    }

    /**
     * @param callable $uptimeCallback
     * @return $this
     */
    public function setUptimeCallback(callable $uptimeCallback)
    {
        $this->uptimeCallback = $uptimeCallback;
        return $this;
    }

    /**
     * @return callable
     */
    public function getUptimeCallback()
    {
        return $this->uptimeCallback;
    }

    /**
     * @param string $lockFilePath
     * @return $this
     */
    public function setLockFilePath($lockFilePath)
    {
        $this->lockFilePath = $lockFilePath;
        return $this;
    }

    /**
     * @return string
     */
    public function getLockFilePath()
    {
        return $this->lockFilePath;
    }

    /**
     * @param string $pidFilePath
     * @return $this
     */
    public function setPidFilePath($pidFilePath)
    {
        $this->pidFilePath = $pidFilePath;
        return $this;
    }

    /**
     * @return string
     */
    public function getPidFilePath()
    {
        return $this->pidFilePath;
    }

    /**
     * @param LoggerInterface $logger
     * @return $this
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        if (null === $this->logger) {
            $this->createLogger();
        }
        return $this->logger;
    }

    /**
     * @return $this
     */
    private function createLogger()
    {
        return $this->setLogger(new Logger($this->getConfig()));
    }

    /**
     * @param StorageInterface $storage
     * @return $this
     */
    public function setStorage(StorageInterface $storage)
    {
        $this->storage = $storage;
        return $this;
    }

    /**
     * @return StorageInterface
     */
    public function getStorage()
    {
        if (null === $this->storage) {
            $this->createStorage();
        }
        return $this->storage;
    }

    /**
     * @return $this
     */
    private function createStorage()
    {
        return $this->setStorage(new Storage($this->getConfig()));
    }
}