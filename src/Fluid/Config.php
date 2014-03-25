<?php
namespace Fluid;

use Serializable;

/**
 * Class Config
 *
 * @package Fluid
 */
class Config implements ConfigInterface, Serializable
{
    /**
     * @var string
     */
    private $adminPath = '/admin/';

    /**
     * @var bool
     */
    private $debug = false;

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

    /**
     * @param string $adminPath
     * @return $this
     */
    public function setAdminPath($adminPath)
    {
        $this->adminPath = $adminPath;
        return $this;
    }

    /**
     * @return string
     */
    public function getAdminPath()
    {
        return $this->adminPath;
    }

    /**
     * @param bool $debug
     * @return $this
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;
        return $this;
    }

    /**
     * @return bool
     */
    public function getDebug()
    {
        return $this->debug;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize([
            'adminPath' => $this->getAdminPath(),
            'debug' => $this->getDebug(),
            'storage' => $this->getStorage(),
            'mapping' => $this->getMapping(),
            'languages' => $this->getLanguages(),
            'language' => $this->getLanguage(),
            'branch' => $this->getBranch(),
            'websocketPort' => $this->getWebsocketPort(),
            'log' => $this->getLog()
        ]);
    }

    /**
     * @param string $serialized
     * @return void
     */
    public function unserialize($serialized)
    {
        $config = unserialize($serialized);
        if (is_array($config)) {
            $this->setAdminPath($config['adminPath']);
            $this->setDebug($config['debug']);
            $this->setStorage($config['storage']);
            $this->setMapping($config['mapping']);
            $this->setLanguages($config['languages']);
            $this->setLanguage($config['language']);
            $this->setBranch($config['branch']);
            $this->setWebsocketPort($config['websocketPort']);
            $this->setLog($config['log']);
        } elseif ($config instanceof Config) {
            $this->setAdminPath($config->getAdminPath());
            $this->setDebug($config->getDebug());
            $this->setStorage($config->getStorage());
            $this->setMapping($config->getMapping());
            $this->setLanguages($config->getLanguages());
            $this->setLanguage($config->getLanguage());
            $this->setBranch($config->getBranch());
            $this->setWebsocketPort($config->getWebsocketPort());
            $this->setLog($config->getLog());
        }
    }
}