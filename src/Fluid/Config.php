<?php
namespace Fluid;

/**
 * Class Config
 *
 * @package Fluid
 */
class Config implements ConfigInterface
{
    /**
     * @var string
     */
    private $storage;

    /**
     * @var string
     */
    private $mapping;

    /**
     * @var array
     */
    private $languages;

    /**
     * @var string
     */
    private $language;

    /**
     * @var string
     */
    private $branch = 'master';

    /**
     * @var int
     */
    private $websocketPort = 8080;

    /**
     * @var string
     */
    private $log;

    /**
     * @param string $branch
     * @return $this
     */
    public function setBranch($branch)
    {
        $this->branch = $branch;
        return $this;
    }

    /**
     * @return string
     */
    public function getBranch()
    {
        return $this->branch;
    }

    /**
     * @param string $language
     * @return $this
     */
    public function setLanguage($language)
    {
        $this->language = $language;
        return $this;
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param array $languages
     * @return $this
     */
    public function setLanguages($languages)
    {
        $this->languages = $languages;
        return $this;
    }

    /**
     * @return array
     */
    public function getLanguages()
    {
        return $this->languages;
    }

    /**
     * @param string $storage
     * @return $this
     */
    public function setStorage($storage)
    {
        $this->storage = $storage;
        return $this;
    }

    /**
     * @return string
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @param string $mapping
     * @return $this
     */
    public function setMapping($mapping)
    {
        $this->mapping = $mapping;
        return $this;
    }

    /**
     * @return string
     */
    public function getMapping()
    {
        return $this->mapping;
    }

    /**
     * @param int $websocketPort
     * @return $this
     */
    public function setWebsocketPort($websocketPort)
    {
        $this->websocketPort = $websocketPort;
        return $this;
    }

    /**
     * @return int
     */
    public function getWebsocketPort()
    {
        return $this->websocketPort;
    }

    /**
     * @param string $log
     * @return $this
     */
    public function setLog($log)
    {
        $this->log = $log;
        return $this;
    }

    /**
     * @return string
     */
    public function getLog()
    {
        return $this->log;
    }
}