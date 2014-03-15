<?php
namespace Fluid;

use Fluid\Map\Map;

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

    //private $branch;
    //private $storage;
    //private static $language = 'en-US';
    //private static $requestPayload;

    /** @var bool|null $controlPannel */
    private static $controlPannel = null;

    /** @var string $controlPannel */
    private static $controlPannelSession;

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
     * @var Map
     */
    private $map;

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
     * @param Map $map
     * @return $this
     */
    public function setMap(Map $map)
    {
        $this->map = $map;
        return $this;
    }

    /**
     * @return Map
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
        $this->setMap(new Map);
        return $this;
    }
}