<?php

namespace Fluid\Storage;

use Fluid\Fluid;

abstract class Cache
{
    protected static $cacheKey;

    /**
     * Store content to file system cache
     *
     * @param mixed $content
     * @return null
     */
    public static function storeCache($content)
    {
        if (empty(static::$cacheKey)) {
            return null;
        }

        file_put_contents(
            Fluid::getBranchStorage() . "/cache/" . static::$cacheKey,
            serialize($content)
        );
    }

    /**
     * Get content from file system cache
     *
     * @return mixed
     */
    public static function getCache()
    {
        if (empty(static::$cacheKey)) {
            return null;
        }

        return
            unserialize(
                file_get_contents(Fluid::getBranchStorage() . "/cache/" . static::$cacheKey)
            );
    }

    /**
     * Checks if a content exists in the cache
     *
     * @return bool
     */
    public static function cacheExists()
    {
        if (empty(static::$cacheKey)) {
            return null;
        }

        if (file_exists(Fluid::getBranchStorage() . "/cache/" . static::$cacheKey)) {
            return true;
        }

        return false;
    }
}