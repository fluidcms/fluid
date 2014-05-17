<?php
namespace Fluid;

use Fluid\Map\MapEntity;
use Fluid\Map\MapMapper;
use Fluid\Daemon\Daemon;
use Fluid\Session\SessionCollection;
use Fluid\Session\SessionEntity;
use Fluid\User\UserCollection;
use Psr\Log\LoggerInterface;
use Fluid\Language\LanguageEntity;

/**
 * The fluid class
 *
 * @package fluid
 */
class Fluid
{
    const VERSION = '0.1.0';

    const DEBUG_OFF = 0;
    const DEBUG_LOG = 1;

    /**
     * @var int
     */
    private $debugMode = self::DEBUG_OFF;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var MapEntity
     */
    private $map;

    /**
     * @var StorageInterface
     * @deprecated Use registry instead
     */
    private $storage;

    /**
     * @var XmlMappingLoaderInterface
     * @deprecated Use registry instead
     */
    private $xmlMappingLoader;

    /**
     * @var Request
     * @deprecated Use registry instead
     */
    private $request;

    /**
     * @var Router
     * @deprecated Use registry instead
     */
    private $router;

    /**
     * @var Event
     * @deprecated Use registry instead
     */
    private $event;

    /**
     * @var LoggerInterface
     * @deprecated Use registry instead
     */
    private $logger;

    /**
     * @var Daemon
     * @deprecated Use registry instead
     */
    private $daemon;

    /**
     * @var bool
     */
    private $isAdmin;

    /**
     * @var string
     */
    private $sessionToken;

    /**
     * @var LanguageEntity
     */
    private $language;

    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config = null)
    {
        if (null !== $config) {
            $this->setConfig($config);
        }
    }

    /**
     * Turns debug mode on
     *
     * @param int $mode
     * @return $this
     * @throws Exception\InvalidDebugModeException
     * @deprecated
     */
    public function debug($mode = self::DEBUG_LOG)
    {
        if ($mode !== self::DEBUG_LOG && $mode !== self::DEBUG_OFF) {
            throw new Exception\InvalidDebugModeException;
        }
        $this->debugMode = $mode;
        return $this;
    }

    /**
     * Get the debug mode
     *
     * @return int
     * @deprecated
     */
    public function getDebugMode()
    {
        return $this->debugMode;
    }

    /**
     * @param TemplateEngineInterface $templateEngine
     * @return $this
     */
    public function setTemplateEngine(TemplateEngineInterface $templateEngine)
    {
        $this->getRegistry()->setTemplateEngine($templateEngine);
        return $this;
    }

    /**
     * @return TemplateEngineInterface
     */
    public function getTemplateEngine()
    {
        return $this->getRegistry()->getTemplateEngine();
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
        if (null === $this->config) {
            $this->createConfig();
        }
        return $this->config;
    }

    /**
     * @return $this
     */
    private function createConfig()
    {
        return $this->setConfig(new Config);
    }

    /**
     * @param MapEntity $map
     * @return $this
     */
    public function setMap(MapEntity $map)
    {
        $this->map = $map;
        return $this;
    }

    /**
     * @return MapEntity
     */
    public function getMap()
    {
        if (null === $this->map) {
            $this->createMap();
        }

        return $this->map;
    }

    /**
     * @return $this
     */
    private function createMap()
    {
        $mapper = new MapMapper($this->getRegistry(), $this->getStorage(), $this->getXmlMappingLoader(), $this->getEvent(), $this->getLanguage());
        return $this->setMap($mapper->map());
    }

    /**
     * @param StorageInterface $storage
     * @return $this
     * @deprecated Use registry instead
     */
    public function setStorage(StorageInterface $storage)
    {
        $this->storage = $storage;
        return $this;
    }

    /**
     * @return StorageInterface
     * @deprecated Use registry instead
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
     * @deprecated Use registry instead
     */
    private function createStorage()
    {
        return $this->setStorage(new Storage($this->getConfig()));
    }

    /**
     * @param XmlMappingLoaderInterface $xmlMappingLoader
     * @return $this
     * @deprecated Use registry instead
     */
    public function setXmlMappingLoader(XmlMappingLoaderInterface $xmlMappingLoader)
    {
        $this->xmlMappingLoader = $xmlMappingLoader;
        return $this;
    }

    /**
     * @return XmlMappingLoaderInterface
     * @deprecated Use registry instead
     */
    public function getXmlMappingLoader()
    {
        if (null === $this->xmlMappingLoader) {
            $this->createXmlMappingLoader();
        }

        return $this->xmlMappingLoader;
    }

    /**
     * @return $this
     * @deprecated Use registry instead
     */
    private function createXmlMappingLoader()
    {
        return $this->setXmlMappingLoader(new XmlMappingLoader($this->getConfig()));
    }

    /**
     * @param Request $request
     * @return $this
     * @deprecated Use registry instead
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return Request
     * @deprecated Use registry instead
     */
    public function getRequest()
    {
        if (null === $this->request) {
            $this->createRequest();
        }

        return $this->request;
    }

    /**
     * @return Request
     * @deprecated Use registry instead
     */
    public function request()
    {
        return $this->getRequest();
    }

    /**
     * @return $this
     * @deprecated Use registry instead
     */
    private function createRequest()
    {
        return $this->setRequest(new Request($this));
    }

    /**
     * @param Router $router
     * @return $this
     * @deprecated Use registry instead
     */
    public function setRouter(Router $router)
    {
        $this->router = $router;
        return $this;
    }

    /**
     * @return Router
     * @deprecated Use registry instead
     */
    public function getRouter()
    {
        if (null === $this->router) {
            $this->createRouter();
        }

        return $this->router;
    }

    /**
     * @return Router
     * @deprecated Use registry instead
     */
    public function router()
    {
        return $this->getRouter();
    }

    /**
     * @return $this
     * @deprecated Use registry instead
     */
    private function createRouter()
    {
        return $this->setRouter(new Router($this->getConfig(), $this->getRequest(), null, $this));
    }

    /**
     * @param Event $event
     * @return $this
     * @deprecated Use registry instead
     */
    public function setEvent(Event $event)
    {
        $this->event = $event;
        return $this;
    }

    /**
     * @return Event
     * @deprecated Use registry instead
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
     * @deprecated Use registry instead
     */
    private function createEvent()
    {
        $event = new Event($this->getConfig(), $this->getLogger());
        $event->setIsAdmin($this->isAdmin());
        $event->setSessionToken($this->sessionToken);
        return $this->setEvent($event);
    }

    /**
     * @param LoggerInterface $logger
     * @return $this
     * @deprecated Use registry instead
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @return LoggerInterface
     * @deprecated Use registry instead
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
     * @deprecated Use registry instead
     */
    private function createLogger()
    {
        return $this->setLogger(new Logger($this->getConfig()));
    }

    /**
     * @param Daemon $daemon
     * @return $this
     * @deprecated Use registry instead
     */
    public function setDaemon(Daemon $daemon)
    {
        $this->daemon = $daemon;
        return $this;
    }

    /**
     * @return Daemon
     * @deprecated Use registry instead
     */
    public function getDaemon()
    {
        if (null === $this->daemon) {
            $this->createDaemon();
        }
        return $this->daemon;
    }

    /**
     * @return $this
     * @deprecated Use registry instead
     */
    private function createDaemon()
    {
        return $this->setDaemon(new Daemon($this->getConfig(), $this->getStorage(), $this->getXmlMappingLoader(), $this->getLogger(), $this->getEvent()));
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
        if (null === $this->isAdmin) {
            $this->setIsAdmin($this->checkIfIsAdmin());
        }
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
     * @return bool
     */
    private function checkIfIsAdmin()
    {
        if (isset($_COOKIE['fluid_session'])) {
            $session = $_COOKIE['fluid_session'];
            if (strlen($session) === SessionEntity::TOKEN_LENGHT && preg_match('/^[a-zA-Z0-9]*$/', $session)) {
                $sessions = new SessionCollection($this->getStorage(), new UserCollection($this->getStorage()));
                $session = $sessions->find($session);
                if ($session instanceof SessionEntity && !$session->isExpired()) {
                    $this->sessionToken = $session->getToken();
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param LanguageEntity $language
     * @return $this
     */
    public function setLanguage(LanguageEntity $language)
    {
        $this->language = $language;
        return $this;
    }

    /**
     * @return LanguageEntity
     */
    public function getLanguage()
    {
        if (null === $this->language) {
            $this->createLanguage();
        }
        return $this->language;
    }

    /**
     * @return $this
     */
    private function createLanguage()
    {
        return $this->setLanguage(new LanguageEntity($this->getConfig()->getLanguage()));
    }

    /**
     * @return RegistryInterface
     */
    public function getRegistry()
    {
        if (null === $this->registry) {
            $this->setRegistry(new Registry);
        }
        return $this->registry;
    }

    /**
     * @param RegistryInterface $registry
     * @return $this
     */
    public function setRegistry(RegistryInterface $registry)
    {
        $this->registry = $registry;
        return $this;
    }
}