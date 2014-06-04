<?php
namespace Fluid;

use Fluid\Data\Data;
use Fluid\Data\DataInterface;
use Fluid\Event\Dispatcher;
use Fluid\Map\MapEntity;
use Fluid\Map\MapMapper;
use Psr\Log\LoggerInterface;

class Registry implements RegistryInterface
{
    /**
     * @var Fluid
     */
    private $fluid;

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
     * @var TemplateEngineInterface
     */
    private $templateEngine;

    /**
     * @var Event
     * @deprecated
     */
    private $event;

    /**
     * @var Dispatcher
     */
    private $dispatcher;

    /**
     * @var MapEntity
     */
    private $map;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var Data
     */
    private $data;

    /**
     * @return Fluid
     */
    public function getFluid()
    {
        return $this->fluid;
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
     * @return ConfigInterface
     */
    public function getConfig()
    {
        if (null === $this->config) {
            $this->setConfig(new Config);
        }
        return $this->config;
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
            $this->setStorage(new Storage($this->getConfig()));
        }

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
        if (null === $this->xmlMappingLoader) {
            $this->setXmlMappingLoader(new XmlMappingLoader($this->getConfig()));
        }

        return $this->xmlMappingLoader;
    }

    /**
     * @param TemplateEngineInterface $templateEngine
     * @return $this
     */
    public function setTemplateEngine(TemplateEngineInterface $templateEngine)
    {
        $this->templateEngine = $templateEngine;
        return $this;
    }

    /**
     * @return null|TemplateEngineInterface
     */
    public function getTemplateEngine()
    {
        if (null === $this->templateEngine) {
            $this->setTemplateEngine(new TemplateEngine($this));
        }
        return $this->templateEngine;
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
            $event = new Event($this->getConfig(), $this->getLogger());
            $event->setIsAdmin($this->getFluid()->isAdmin());
            $event->setSessionToken($this->getFluid()->getSessionToken());
            $this->setEvent($event);
        }
        return $this->event;
    }

    /**
     * @param Dispatcher $dispatcher
     * @return $this
     */
    public function setEventDispatcher(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
        return $this;
    }

    /**
     * @return Dispatcher
     */
    public function getEventDispatcher()
    {
        if (null === $this->dispatcher) {
            $this->setEventDispatcher(new Dispatcher());
        }
        return $this->dispatcher;
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
            $mapper = new MapMapper($this, $this->getStorage(), $this->getXmlMappingLoader(), $this->getEvent(), $this->getFluid()->getLanguage());
            $this->setMap($mapper->map());
        }

        return $this->map;
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
            $this->setLogger(new Logger($this->getConfig()));
        }
        return $this->logger;
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
            $this->setRouter(new Router($this, $this->getConfig(), $this->getFluid()->getRequest()));
        }

        return $this->router;
    }

    /**
     * @return DataInterface
     */
    public function getData()
    {
        if (null === $this->data) {
            $this->setData(new Data);
        }
        return $this->data;
    }

    /**
     * @param DataInterface $data
     * @return $this
     */
    public function setData(DataInterface $data)
    {
        $this->data = $data;
        return $this;
    }
}