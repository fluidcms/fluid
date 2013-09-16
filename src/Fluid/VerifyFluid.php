<?php

namespace Fluid;

use PDOException;

/**
 * Init a new project
 *
 * @package fluid
 */
class VerifyFluid
{
    /**
     * Check if we can run Fluid
     *
     * @return  bool
     */
    public static function check()
    {
        $ready = true;
        if (!self::checkStorage()) {
            $ready = false;
        }
        if (!self::checkGit()) {
            $ready = false;
        }
        if (!self::checkDatabase()) {
            $ready = false;
        }
        return $ready;
    }

    /**
     * Check if the storage folder exists
     *
     * @return  bool
     */
    public static function checkStorage()
    {
        $dir = Fluid::getConfig('storage');
        // Base dir
        if (!is_dir($dir)) {
            if (mkdir($dir, 0777, true)) {
                return true;
            }
        } else {
            return true;
        }

        return false;
    }

    /**
     * Check if git is initalized
     *
     * @return  bool
     */
    public static function checkGit()
    {
        return (
            is_dir(Fluid::getConfig('storage') . 'master/.git')
        );
    }
}