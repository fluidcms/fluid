<?php
namespace Fluid;

use Fluid\Data\DataMapper;
use Fluid\Event\Dispatcher;
use Fluid\Map\MapEntity;
use Psr\Log\LoggerInterface;

interface RegistryInterface
{
    /**
     * @return Fluid
     */
    public function getFluid();

    /**
     * @param Fluid $fluid
     * @return $this
     */
    public function setFluid(Fluid $fluid);

    /**
     * @return ConfigInterface
     */
    public function getConfig();

    /**
     * @param ConfigInterface $config
     * @return $this
     */
    public function setConfig(ConfigInterface $config);

    /**
     * @return StorageInterface
     */
    public function getStorage();

    /**
     * @param StorageInterface $storage
     * @return $this
     */
    public function setStorage(StorageInterface $storage);

    /**
     * @return XmlMappingLoaderInterface
     */
    public function getXmlMappingLoader();

    /**
     * @param XmlMappingLoaderInterface $xmlMappingLoader
     * @return $this
     */
    public function setXmlMappingLoader(XmlMappingLoaderInterface $xmlMappingLoader);

    /**
     * @param TemplateEngineInterface $templateEngine
     * @return $this
     */
    public function setTemplateEngine(TemplateEngineInterface $templateEngine);

    /**
     * @return TemplateEngineInterface
     */
    public function getTemplateEngine();

    /**
     * @param Event $event
     * @return $this
     * @deprecated
     */
    public function setEvent(Event $event);

    /**
     * @return Event
     * @deprecated
     */
    public function getEvent();

    /**
     * @param Dispatcher $dispatcher
     * @return $this
     */
    public function setEventDispatcher(Dispatcher $dispatcher);

    /**
     * @return Dispatcher
     */
    public function getEventDispatcher();

    /**
     * @param MapEntity $map
     * @return $this
     */
    public function setMap(MapEntity $map);

    /**
     * @return MapEntity
     */
    public function getMap();

    /**
     * @param LoggerInterface $logger
     * @return $this
     */
    public function setLogger(LoggerInterface $logger);

    /**
     * @return LoggerInterface
     */
    public function getLogger();

    /**
     * @param Router $router
     * @return $this
     */
    public function setRouter(Router $router);

    /**
     * @return Router
     */
    public function getRouter();

    /**
     * @param DataMapper $dataMapper
     * @return $this
     */
    public function setDataMapper(DataMapper $dataMapper);

    /**
     * @return DataMapper
     */
    public function getDataMapper();
}