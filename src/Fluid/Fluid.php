<?php
namespace Fluid;

use Fluid\Map\MapEntity;
use Fluid\Map\MapMapper;

/**
 * The fluid class
 *
 * @package fluid
 */
class Fluid
{
    const VERSION = '1.0.0';

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
    public function createMap()
    {
        $map = new MapEntity;
        $mapMapper = new MapMapper($this->getStorage(), $this->getXmlMappingLoader());
        $map->setMapper($mapMapper);
        $mapMapper->map($map);
        return $this->setMap(new MapEntity);
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
    public function createStorage()
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
    public function createXmlMappingLoader()
    {
        return $this->setXmlMappingLoader(new XmlMappingLoader($this));
    }
}