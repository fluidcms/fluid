<?php

namespace Fluid\Daemons;
use Fluid;
use Fluid\WebSocket\Tasks;
use Fluid\WebSocket\Server;
use Fluid\WebSocket\Events as ServerEvents;
use Exception;
use React;
use Ratchet;
use ZMQ;
use ZMQSocketException;
use React\EventLoop\LibEventLoop;
use Fluid\MessageQueue;

class WebSocketServer extends Fluid\Daemon implements Fluid\DaemonInterface
{
    private static $lockFile = '.server.lock';

    private $instanceId;
    private $lock;
    protected $statusFile = 'WebSocketStatus.txt';

    /**
     * Check if daemon is running
     *
     * @return  bool
     */
    public static function isRunning()
    {
        $dir = Fluid\Fluid::getConfig('storage');
        $handler = fopen($dir . self::$lockFile, "w+");

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
    public static function start()
    {
        $instanceId = uniqid();
        $debugMode = Fluid\Fluid::getDebugMode();
        $timeZone = date_default_timezone_get();
        shell_exec(
            "php -q " . __DIR__ . "/StartDaemon.php WebSocketServer " .
            base64_encode(serialize(Fluid\Fluid::getConfig())) . " " .
            $instanceId . " " .
            $debugMode . " " .
            base64_encode($timeZone) .
            " > /dev/null &"
        );

        $dir = Fluid\Fluid::getConfig('storage');
        $i = 0;
        while ($i < 100) {
            $fileContent = file_get_contents($dir . self::$lockFile);
            if ($fileContent === $instanceId) {
                return true;
            }
            $i++;
            usleep(100000);
        }
        return false;
    }

    /**
     * Init
     */
    public function __construct($instanceId = "")
    {
        $this->instanceId = $instanceId;
    }

    /**
     * Create and lock the lock file
     *
     * @return  mixed
     */
    private function lock()
    {
        $dir = Fluid\Fluid::getConfig('storage');

        if (!is_dir($dir)) {
            mkdir($dir);
        }

        $this->lock = fopen($dir . self::$lockFile, "w+");

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
        $dir = Fluid\Fluid::getConfig('storage');

        file_put_contents($dir . self::$lockFile, "");

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

        $this->upTimeCallback();

        $server = new Server;
        $tasks = new Tasks($server);
        $tasks->execute();

        new ServerEvents($server);

        $loop = React\EventLoop\Factory::create();

        $root = $this;
        $loop->addPeriodicTimer(1, function() use ($root, $server, $tasks) {
            // Stops server if no one is using it
            if ($server->isInactive()) {
                exit;
            }
            // Render websocket server status
            $root->renderStatus($server);
            // Execute tasks
            $tasks->execute();
        });

        $loop->addPeriodicTimer(10, function() use ($root) {
            $root->upTimeCallback();
        });

        $this->zmqServer($loop, $tasks);

        $this->websocketServer($loop, $server);

        $this->renderStatus($server);

        file_put_contents(Fluid\Fluid::getConfig('storage') . self::$lockFile, $this->instanceId);
        $loop->run();

        $this->release();
    }

    /**
     * Create the ZeroMQ server
     *
     * @param   LibEventLoop    $loop
     * @param   Tasks   $tasks
     * @return  void
     */
    private function zmqServer(LibEventLoop $loop, Tasks $tasks)
    {
        // Get a port
        $port = MessageQueue::getAvalaiblePort();

        $context = new React\ZMQ\Context($loop);
        $pull = $context->getSocket(ZMQ::SOCKET_PULL);
        $pull->bind("tcp://127.0.0.1:{$port}");
        $pull->on('message', array($tasks, 'message'));
    }

    /**
     * Create the WebSocket server
     *
     * @param   LibEventLoop    $loop
     * @param   Server  $server
     * @return  void
     */
    private function websocketServer(LibEventLoop $loop, Server $server)
    {
        $port = Fluid\Fluid::getConfig('websocket');

        $socket = new React\Socket\Server($loop);
        $socket->listen($port, '0.0.0.0');

        new Ratchet\Server\IoServer(
            new Ratchet\WebSocket\WsServer(
                new Ratchet\Wamp\WampServer(
                    $server
                )
            ),
            $socket
        );
    }

    /**
     * Display daemon status
     *
     * @param   Server $server
     * @return  void
     */
    private function renderStatus(Server $server)
    {
        if ($this->displayStatus) {
            $status = $this->status;
            $status = str_replace('%uptime', $this->getReadableUpTime(), $status);
            $status = str_replace('%connections', count($server->getConnections()), $status);
            $status = str_replace('%memory', $this->getReadableMemoryUsage(), $status);
            $this->displayStatus($status);
        }
    }
}
