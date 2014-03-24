<?php
namespace Fluid;

use Fluid\Map\MapEntity;
use Fluid\Map\MapMapper;
use Fluid\Daemon\Daemon;

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
     * @var TemplateEngineInterface
     */
    private $templateEngine;

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
     */
    private $storage;

    /**
     * @var XmlMappingLoaderInterface
     */
    private $xmlMappingLoader;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var Event
     */
    private $event;

    /**
     * @var Daemon
     */
    private $daemon;

    public function __construct()
    {
        $this->setConfig(new Config);
        $this->setTemplateEngine(new TemplateEngine);
    }

    /**
     * Turns debug mode on
     *
     * @param int $mode
     * @return $this
     * @throws Exception\InvalidDebugModeException
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
     */
    public function getDebugMode()
    {
        return $this->debugMode;
    }

    /**
     * @param null|TemplateEngineInterface $templateEngine
     * @return $this
     */
    public function setTemplateEngine(TemplateEngineInterface $templateEngine = null)
    {
        $this->templateEngine = $templateEngine;
        return $this;
    }

    /**
     * @return null|TemplateEngineInterface
     */
    public function getTemplateEngine()
    {
        return $this->templateEngine;
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
        $mapper = new MapMapper($this->getStorage(), $this->getXmlMappingLoader());
        return $this->setMap($mapper->map());
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
        return $this->setStorage(new Storage($this));
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
        if (null === $this->xmlMappingLoader) {
            $this->createXmlMappingLoader();
        }

        return $this->xmlMappingLoader;
    }

    /**
     * @return $this
     */
    private function createXmlMappingLoader()
    {
        return $this->setXmlMappingLoader(new XmlMappingLoader($this));
    }

    /**
     * @param Request $request
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return Request
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
     */
    public function request()
    {
        return $this->getRequest();
    }

    /**
     * @return $this
     */
    private function createRequest()
    {
        return $this->setRequest(new Request($this));
    }

    /**
     * @param Router $router
     * @return $this
     */
    public function setRouter(Router $router)
    {
        $this->router = $router;
        return $this;
    }

    /**
     * @return Router
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
     */
    public function router()
    {
        return $this->getRouter();
    }

    /**
     * @return $this
     */
    private function createRouter()
    {
        return $this->setRouter(new Router($this, $this->getRequest()));
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
     * @param Daemon $daemon
     * @return $this
     */
    public function setDaemon(Daemon $daemon)
    {
        $this->daemon = $daemon;
        return $this;
    }

    /**
     * @return Daemon
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
     */
    private function createDaemon()
    {
        return $this->setDaemon(new Daemon($this->getConfig(), $this->getEvent()));
    }
}