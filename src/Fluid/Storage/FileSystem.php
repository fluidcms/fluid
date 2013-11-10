<?php

namespace Fluid\Storage;

use Fluid\Fluid;

/**
 * File storage helper class
 *
 * @package fluid
 */
abstract class FileSystem extends Cache
{
    protected static $dataFile;

    /**
     * Get data from storage
     *
     * @param string|null $file
     * @return array
     */
    public static function load($file = null)
    {
        if (self::cacheExists()) {
            //return self::getCache();
        }

        if (null === $file) {
            $file = static::$dataFile;
        }

        $file = Fluid::getBranchStorage() . '/' . $file;

        if (file_exists($file)) {
            return json_decode(file_get_contents($file), true);
        }

        return array();
    }

    /**
     * Save data to storage
     *
     * @param string $content
     * @param mixed|null $file
     */
    public static function save($content, $file = null)
    {
        if (null === $file) {
            $file = static::$dataFile;
        }

        $dir = Fluid::getBranchStorage() . '/' . dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir);
        }

        $file = Fluid::getBranchStorage() . '/' . $file;

        file_put_contents($file, $content);

        //self::storeCache($content);
    }

    /**
     * Set the data file
     *
     * @param string
     */
    public static function setDataFile($file)
    {
        static::$dataFile = $file;
    }

    /**
     * Get the data file
     *
     * @return string
     */
    public static function getDataFile()
    {
        return static::$dataFile;
    }
}