<?php
namespace Fluid\Daemon;

use Symfony\Component\Routing\RouteCollection;
use React\Socket\Server as Reactor;
use React\EventLoop\Factory as Loop;
use React\EventLoop\StreamSelectLoop;
use Ratchet\Wamp\WampServerInterface;
use Fluid\Config;
use Symfony\Component\Routing\Route;
use Ratchet\WebSocket\WsServer;
use Ratchet\Wamp\WampServer;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\Http\Router;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;

class Server
{
    /** @var RouteCollection $routes */
    private $routes;

    /** @var StreamSelectLoop $loop */
    private $loop;

    private $socket;

    private $port;

    function __construct()
    {
        $this->setLoop(Loop::create())
            ->setPort(Config::get('websocket'))
            ->setRoutes(new RouteCollection)
            ->setSocket(new Reactor($this->loop));
    }

    /**
     * @param WampServerInterface $server
     * @param $path
     */
    public function add(WampServerInterface $server, $path)
    {
        $name = str_replace('/', '_', trim($path, '/'));
        $this->routes
            ->add(
                $name,
                new Route(
                    $path,
                    array('_controller' => new WsServer(new WampServer($server)))
                )
            );
    }

    public function create()
    {
        $this->getSocket()->listen($this->getPort());

        new IoServer(
            new HttpServer(
                new Router(
                    new UrlMatcher($this->routes, new RequestContext)
                )
            ),
            $this->socket,
            $this->loop
        );
    }

    public function run()
    {
        $this->loop->run();
    }

    /**
     * Release websocket server
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Release websocket server
     */
    public function close()
    {
        $socket = $this->getSocket();
        if ($socket && $socket->master) {
            $socket->shutdown();
            $this->setSocket(null);
        }
    }

    /**
     * @param \React\EventLoop\StreamSelectLoop $loop
     * @return self
     */
    public function setLoop($loop)
    {
        $this->loop = $loop;
        return $this;
    }

    /**
     * @return \React\EventLoop\StreamSelectLoop
     */
    public function getLoop()
    {
        return $this->loop;
    }

    /**
     * @param mixed $port
     * @return self
     */
    public function setPort($port)
    {
        $this->port = $port;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param \Symfony\Component\Routing\RouteCollection $routes
     * @return self
     */
    public function setRoutes($routes)
    {
        $this->routes = $routes;
        return $this;
    }

    /**
     * @return \Symfony\Component\Routing\RouteCollection
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * @param \React\Socket\Server $socket
     * @return self
     */
    public function setSocket($socket)
    {
        $this->socket = $socket;
        return $this;
    }

    /**
     * @return \React\Socket\Server
     */
    public function getSocket()
    {
        return $this->socket;
    }
}