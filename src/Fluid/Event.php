<?php
namespace Fluid;

use InvalidArgumentException;
use Fluid\WebsocketClient\EventWebsocketClient;
use Psr\Log\LoggerInterface;

class Event
{
    /**
     * @var array
     */
    private $listeners = [];

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var bool
     */
    private $isAdmin;

    /**
     * @param ConfigInterface $config
     * @param LoggerInterface $logger
     */
    public function __construct(ConfigInterface $config = null, LoggerInterface $logger = null)
    {
        if (null !== $config) {
            $this->setConfig($config);
        }
        if (null !== $logger) {
            $this->setLogger($logger);
        }
    }

    /**
     * Trigger an event to the websocket server
     *
     * @param string $event
     * @param array $arguments
     * @throws InvalidArgumentException
     * @return array
     */
    public function triggerWebsocketEvent($event, array $arguments = [])
    {
        if ($this->isAdmin()) {
            $eventWebsocketClient = new EventWebsocketClient($this->getConfig(), $this->getLogger());
            $eventWebsocketClient->send($event, $arguments);
        }
    }

    /**
     * Bind an event.
     *
     * @param string $event
     * @param callable $callback
     * @throws InvalidArgumentException
     * @return bool
     */
    public function on($event, callable $callback)
    {
        // Check if event is a string
        if (!is_string($event) || !is_callable($callback)) {
            throw new InvalidArgumentException();
        }

        if (!isset($this->listeners[$event])) {
            $this->listeners[$event] = [];
        }

        $this->listeners[$event][] = $callback;

        return true;
    }

    /**
     * Trigger an event
     *
     * @param string $event
     * @param array $arguments
     * @throws InvalidArgumentException
     * @return array
     */
    public function trigger($event, array $arguments = [])
    {
        if (!is_string($event) || !is_array($arguments)) {
            throw new InvalidArgumentException();
        }

        $retval = [];

        if (isset($this->listeners[$event])) {
            foreach ($this->listeners[$event] as $listener) {
                $retval[] = call_user_func_array($listener, $arguments);
            }
        }

        return $retval;
    }

    /**
     * @param bool $isAdmin
     * @return $this
     */
    public function setIsAdmin($isAdmin)
    {
        $this->isAdmin = $isAdmin;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsAdmin()
    {
        return $this->isAdmin;
    }

    /**
     * @return bool
     */
    public function isAdmin()
    {
        return $this->getIsAdmin();
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
        return $this->logger;
    }
}