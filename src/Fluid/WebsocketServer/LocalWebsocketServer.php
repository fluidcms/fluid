<?php
namespace Fluid\WebsocketServer;

use Fluid\Request;
use Fluid\Response;
use Fluid\Router;
use Fluid\Session\SessionEntity;
use Fluid\User\UserCollection;
use Fluid\Session\SessionCollection;
use Fluid\User\UserEntity;
use Ratchet\Wamp\WampServerInterface;
use Fluid\ConfigInterface;
use Fluid\StorageInterface;
use Fluid\XmlMappingLoaderInterface;
use Fluid\Event;
use Fluid\Fluid;
use Ratchet;
use React;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampConnection;
use Ratchet\Wamp\Topic;
use Exception;
use Psr\Log\LoggerInterface;

/**
 * WebSocket Server for receiving and sending communications to the local server
 *
 * @package Fluid
 */
class LocalWebSocketServer implements WampServerInterface
{
    const URI = 'websocket';

    const TYPE_ID_WELCOME = 0;
    const TYPE_ID_PREFIX = 1;
    const TYPE_ID_CALL = 2;
    const TYPE_ID_CALLRESULT = 3;
    const TYPE_ID_ERROR = 4;
    const TYPE_ID_SUBSCRIBE = 5;
    const TYPE_ID_UNSUBSCRIBE = 6;
    const TYPE_ID_PUBLISH = 7;
    const TYPE_ID_EVENT = 8;

    /**
     * @var array
     */
    protected $connections = [];

    /**
     * @var int
     */
    private $startTime;

    /**
     * @var bool
     */
    private $hadConnections = false;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var XmlMappingLoaderInterface
     */
    private $xmlMappingLoader;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Event
     */
    private $event;

    /**
     * @var UserCollection
     */
    private $users;

    /**
     * @var SessionCollection
     */
    private $sessions;

    /**
     * @var Fluid
     */
    private $fluid;

    /**
     * @param ConfigInterface $config
     * @param StorageInterface $storage
     * @param XmlMappingLoaderInterface $xmlMappingLoader
     * @param LoggerInterface $logger
     * @param Event $event
     * @param Fluid $fluid
     */
    public function __construct(ConfigInterface $config, StorageInterface $storage, XmlMappingLoaderInterface $xmlMappingLoader, LoggerInterface $logger, Event $event, Fluid $fluid = null)
    {
        $this->startTime = time();
        $this->setConfig($config);
        $this->setStorage($storage);
        $this->setXmlMappingLoader($xmlMappingLoader);
        $this->setLogger($logger);
        $this->setEvent($event);
        if (null !== $fluid) {
            $this->setFluid($fluid);
        }
        $this->bindEvents();
    }

    private function bindEvents()
    {
        $root = $this;

        // User loads data from a page
        $this->getEvent()->on('website:page:change', function($sessionToken, $page) use ($root) {
            $root->sendToSession($sessionToken, [
                'target' => 'website:page:change',
                'data' => [
                    'page' => $page
                ]
            ]);
        });

        // User loads data from a page
        // @deprecated
        $this->getEvent()->on('data:get', function($session, $language, $page) use ($root) {
            $root->sendToSession($session, [
                'target' => 'data_request',
                'data' => [
                    'language' => $language,
                    'page' => $page
                ]
            ]);
        });

        // User loads data from a page
        // @deprecated
        $this->getEvent()->on('language:changed', function($session, $language) use ($root) {
            $root->sendToSession($session, [
                'target' => 'language_detected',
                'data' => [
                    'language' => $language
                ]
            ]);
        });
    }

    /**
     * Get active connections
     *
     * @return  array
     */
    public function getConnections()
    {
        return $this->connections;
    }

    /**
     * Determin if the server is inactive
     *
     * @return bool
     */
    public function isInactive()
    {
        if ($this->countConnections()) {
            return false;
        }

        if ($this->hadConnections) {
            return true;
        }

        if ($this->startTime < time() - 30) { // Inactive for 30 seconds
            return true;
        }

        return false;
    }

    /**
     * @return int
     */
    public function countConnections()
    {
        return count($this->connections);
    }

    /**
     * Send message to user using his session token
     *
     * @param string $session
     * @param mixed $message
     */
    public function sendToSession($session, $message)
    {
        if (is_array($message)) {
            $message = json_encode($message);
        }

        // todo
        //$this->getLogger()->debug('');

        foreach ($this->getConnections() as $connection) {
            if (isset($connection['session']) && $session === $connection['session']) {
                /** @var Topic $connection */
                $connection = $connection['connection'];
                $connection->broadcast($message);
            }
        }
    }

    /**
     * Subscribe a user to a topic, users are subscribed based on their actions server side and do not subscribe to
     * topics client side
     *
     * @param string $userId
     * @param string $topic
     */
    public function subscribe($userId, $topic)
    {
        foreach ($this->getConnections() as $connection) {
            if (isset($connection['user_id']) && $userId === $connection['user_id']) {
                $connection['topics'][] = $topic;
            }
        }
    }

    /**
     * @param ConnectionInterface|WampConnection $conn
     * @param Topic|string $topic
     */
    public function onSubscribe(ConnectionInterface $conn, $topic)
    {
        $data = json_decode($topic->getId(), true);

        if (!array_key_exists($topic->getId(), $this->connections[$conn->WAMP->sessionId])) {
            $this->connections[$conn->WAMP->sessionId] = array(
                'connection' => $topic,
                'session' => $data['session'],
                'branch' => $data['branch'],
                'user_id' => $data['user_id'],
                'topics' => array()
            );
            $this->getLogger()->debug("User {$data['user_id']} subscribed");
            $topic->broadcast('true');
        }
    }

    /**
     * @param ConnectionInterface|WampConnection $conn
     * @param Topic|string $topic
     */
    public function onUnSubscribe(ConnectionInterface $conn, $topic)
    {
        $this->getLogger()->debug("User unsubscribed");
        unset($this->connections[$conn->WAMP->sessionId]);
    }

    /**
     * @param ConnectionInterface|WampConnection $conn
     */
    public function onOpen(ConnectionInterface $conn)
    {
        $this->hadConnections = true;
        $this->connections[$conn->WAMP->sessionId] = array();

        $this->getLogger()->debug("User opened connection " . $conn->WAMP->sessionId);
        $this->getEvent()->trigger('websocket:connection:open', array('conn' => $conn));
    }

    /**
     * @param ConnectionInterface|WampConnection $conn
     */
    public function onClose(ConnectionInterface $conn)
    {
        unset($this->connections[$conn->WAMP->sessionId]);

        $this->getLogger()->debug("User closed connection " . $conn->WAMP->sessionId);
        $this->getEvent()->trigger('websocket:connection:close', array('conn' => $conn));
    }

    /**
     * @param ConnectionInterface|WampConnection $conn
     * @param string $id
     * @param Topic|string $topic
     * @param array $params
     */
    public function onCall(ConnectionInterface $conn, $id, $topic, array $params)
    {
        // Ping
        if (isset($params['ping'])) {
            $conn->send('true');
            return;
        }

        if (
            isset($params['uri']) &&
            isset($params['method']) &&
            isset($params['params']) &&
            is_string($params['uri']) &&
            is_string($params['method']) &&
            is_array($params['params'])
        ) {
            $data = json_decode($topic, true);

            $this->getLogger()->debug("User {$data['user_id']} called method {$params['method']} {$params['uri']}");

            $session = $this->getSessions()->find($data['session']);
            $user = $this->getUsers()->find($data['user_id']);

            if ($session instanceof SessionEntity && $user instanceof UserEntity) {
                $request = new Request;
                $request->setMethod($params['method']);
                $request->setParams($params['params']);

                $uri = $params['uri'];
                if (strpos($uri, '/') !== 0) {
                    $uri = '/' . $uri;
                }
                $request->setUri($uri);

                $response = new Response;

                $router = new Router($this->getConfig(), $request, $response, $this->getFluid());
                $router->dispatchLocalWebsocketRouter($this->getStorage(), $this->getXmlMappingLoader(), $this->getUsers(), $user, $this->getSessions(), $session);

                $conn->send(json_encode([self::TYPE_ID_CALLRESULT, $id, $response->getBody()]));
                return;
            }

        }

        $conn->callError($id, $topic, 'Invalid call')->close();
    }

    /**
     * @param ConnectionInterface|WampConnection $conn
     * @param Topic|string $topic
     * @param string $event
     * @param array $exclude
     * @param array $eligible
     */
    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible)
    {
        $this->getLogger()->debug("User published message");
    }

    /**
     * @param ConnectionInterface|WampConnection $conn
     * @param Exception $e
     */
    public function onError(ConnectionInterface $conn, Exception $e)
    {
        $this->getLogger()->debug("Local Websocket Server Error (" . $e->getCode() . "): " . $e->getMessage());
        unset($this->connections[$conn->WAMP->sessionId]);
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
        return $this->event;
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
        return $this->storage;
    }

    /**
     * @param XmlMappingLoaderInterface $xmlMappingLoader
     * @return $this
     */
    public function setXmlMappingLoader(XmlMappingLoaderInterface $xmlMappingLoader)
    {
        $this->xmlMappingLoader = $xmlMappingLoader;
        return $this;
    }

    /**
     * @return XmlMappingLoaderInterface
     */
    public function getXmlMappingLoader()
    {
        return $this->xmlMappingLoader;
    }

    /**
     * @param UserCollection $users
     * @return $this
     */
    public function setUsers(UserCollection $users)
    {
        $this->users = $users;
        return $this;
    }

    /**
     * @return UserCollection
     */
    public function getUsers()
    {
        if (null === $this->users) {
            $this->createUsers();
        }
        return $this->users;
    }

    /**
     * @return $this
     */
    private function createUsers()
    {
        return $this->setUsers(new UserCollection($this->getStorage()));
    }

    /**
     * @param SessionCollection $sessions
     * @return $this
     */
    public function setSessions(SessionCollection $sessions)
    {
        $this->sessions = $sessions;
        return $this;
    }

    /**
     * @return SessionCollection
     */
    public function getSessions()
    {
        if (null === $this->sessions) {
            $this->createSessions();
        }
        return $this->sessions;
    }

    /**
     * @return $this
     */
    private function createSessions()
    {
        return $this->setSessions(new SessionCollection($this->getStorage(), $this->getUsers()));
    }

    /**
     * @param Fluid $fluid
     * @return $this
     */
    public function setFluid(Fluid $fluid)
    {
        $this->fluid = $fluid;
        return $this;
    }

    /**
     * @return Fluid
     */
    public function getFluid()
    {
        if (null === $this->fluid) {
            $this->createFluid();
        }
        return $this->fluid;
    }

    /**
     * @return $this
     */
    private function createFluid()
    {
        $fluid = new Fluid($this->getConfig());
        $fluid->setLogger($this->getLogger());
        $fluid->setEvent($this->getEvent());
        $fluid->setStorage($this->getStorage());
        return $this->setFluid($fluid);
    }
}