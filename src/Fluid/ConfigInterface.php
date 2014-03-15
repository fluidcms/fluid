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
     * @param string $structure
     * @return $this
     */
    public function setStructure($structure);

    /**
     * @return string
     */
    public function getStructure();

    /**
     * @param string $log
     * @return $this
     */
    public function setLog($log);

    /**
     * @return string
     */
    public function getLog();
}