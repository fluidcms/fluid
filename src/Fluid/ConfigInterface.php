<?php
namespace Fluid;

/**
 * Interface ConfigInterface
 *
 * @package Fluid
 */
interface ConfigInterface
{
    /**
     * @param string $adminPath
     * @return $this
     */
    public function setAdminPath($adminPath);

    /**
     * @return string
     */
    public function getAdminPath();

    /**
     * @param string $branch
     * @return $this
     */
    public function setBranch($branch);

    /**
     * @return string
     */
    public function getBranch();

    /**
     * @param string $language
     * @return $this
     */
    public function setLanguage($language);

    /**
     * @return string
     */
    public function getLanguage();

    /**
     * @param array $languages
     * @return $this
     */
    public function setLanguages($languages);

    /**
     * @return array
     */
    public function getLanguages();

    /**
     * @param string $storage
     * @return $this
     */
    public function setStorage($storage);

    /**
     * @return string
     */
    public function getStorage();

    /**
     * @param string $mapping
     * @return $this
     */
    public function setMapping($mapping);

    /**
     * @return string
     */
    public function getMapping();

    /**
     * @param int $websocketPort
     * @return $this
     */
    public function setWebsocketPort($websocketPort);

    /**
     * @return int
     */
    public function getWebsocketPort();

    /**
     * @param string $log
     * @return $this
     */
    public function setLog($log);

    /**
     * @return string
     */
    public function getLog();

    /**
     * @return string
     */
    public function serialize();

    /**
     * @param string $serialized
     * @return void
     */
    public function unserialize($serialized);
}