<?php
namespace Fluid;

use Symfony\Component\Routing\RouteCollection;
use React\Socket\Server as ReactSocketServer;
use React\EventLoop\Factory as ReactLoopFactory;
use React\EventLoop\StreamSelectLoop as ReactEventLoop;
use Ratchet\Wamp\WampServerInterface;
use Symfony\Component\Routing\Route;
use Ratchet\WebSocket\WsServer;
use Ratchet\Wamp\WampServer;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\Http\Router;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;

class WebsocketServer
{
    /**
     * @var RouteCollection
     */
    private $routes;

    /**
     * @var ReactEventLoop
     */
    private $reactEventLoop;

    /**
     * @var ReactSocketServer
     */
    private $reactSocketServer;

    /**
     * @var int
     */
    private $port;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     */
    function __construct(Config $config)
    {
        $this->setConfig($config);
        $this->setPort($config->getWebsocketPort());
        $this->setReactEventLoop(ReactLoopFactory::create());
        $this->setRoutes(new RouteCollection);
        $this->setReactSocketServer(new ReactSocketServer($this->getReactEventLoop()));
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
        $this->getReactSocketServer()->listen($this->getPort());

        new IoServer(
            new HttpServer(
                new Router(
                    new UrlMatcher($this->routes, new RequestContext)
                )
            ),
            $this->getReactSocketServer(),
            $this->getReactEventLoop()
        );
    }

    public function run()
    {
        $this->getReactEventLoop()->run();
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
        $socket = $this->getReactSocketServer();
        if ($socket && $socket->master) {
            $socket->shutdown();
            $this->setReactSocketServer(null);
        }
    }

    /**
     * @param int $port
     * @return self
     */
    public function setPort($port)
    {
        $this->port = $port;
        return $this;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param RouteCollection $routes
     * @return self
     */
    public function setRoutes($routes)
    {
        $this->routes = $routes;
        return $this;
    }

    /**
     * @return RouteCollection
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * @param ReactSocketServer $reactSocketServer
     * @return self
     */
    public function setReactSocketServer($reactSocketServer)
    {
        $this->reactSocketServer = $reactSocketServer;
        return $this;
    }

    /**
     * @return ReactSocketServer
     */
    public function getReactSocketServer()
    {
        return $this->reactSocketServer;
    }

    /**
     * @param ReactEventLoop $reactEventLoop
     * @return $this
     */
    public function setReactEventLoop(ReactEventLoop $reactEventLoop)
    {
        $this->reactEventLoop = $reactEventLoop;
        return $this;
    }

    /**
     * @return ReactEventLoop
     */
    public function getReactEventLoop()
    {
        return $this->reactEventLoop;
    }

    /**
     * @param Config $config
     * @return $this
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }
}