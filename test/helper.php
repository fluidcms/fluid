<?php

namespace Fluid\Tests;

use Fluid\Fluid;

class Helper
{
    public static function getUser()
    {
        return array(
            "id" => "xxxxxxxxxxxxx",
            "name" => "PHPUnit",
            "email" => "phpunit@localhost"
        );
    }

    public static function getFixtureDir()
    {
        return __DIR__ . "/Fluid/Tests/Fixtures";
    }

    public static function getStorage()
    {
        return self::getFixtureDir() . "/storage/develop";
    }

    public static function copyStorage()
    {
        Fluid::setConfig('database', array(
                'config'	=> array(
                    'driver'     => 'mysql',
                    'host'       => $GLOBALS['DB_HOST'],
                    'dbname'     => $GLOBALS['DB_DBNAME'],
                    'user'       => $GLOBALS['DB_USER'],
                    'password'   => $GLOBALS['DB_PASSWD']
                )
            )
        );

        $copy = function($dir = null, $dest = null) use (&$copy) {
            if (null === $dir) {
                $dir = __DIR__ . "/Fluid/Tests/Fixtures/storage/master";
            }

            if (null === $dest) {
                $dest = __DIR__ . "/Fluid/Tests/Fixtures/storage/develop";
            }

            if (!is_dir($dest)) {
                mkdir($dest);
            }

            foreach(scandir($dir) as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                } else if (is_file($dir . "/" . $file)) {
                    copy($dir . "/" . $file, $dest . "/" . $file);
                } else if (is_dir($dir . "/" . $file)) {
                    $copy($dir . "/" . $file, $dest . "/" . $file);
                }
            }
        };

        if (is_dir(__DIR__ . "/Fluid/Tests/Fixtures/storage/develop")) {
            self::deleteStorage();
        }

        $copy();

        exec("git init ". self::getStorage());
        exec("git --git-dir=".self::getStorage()."/.git --work-tree=".self::getStorage()." add ".self::getStorage()."/*");
        exec("git --git-dir=".self::getStorage()."/.git --work-tree=".self::getStorage()." commit -m initial\\ commit");

        Fluid::setBranch('develop');
    }

    public static function deleteStorage($dir = null)
    {
        if (null === $dir) {
            $dir = __DIR__ . "/Fluid/Tests/Fixtures/storage/develop";
        }

        foreach(scandir($dir) as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            } else if (is_file($dir . "/" . $file)) {
                unlink($dir . "/" . $file);
            } else if (is_dir($dir . "/" . $file)) {
                self::deleteStorage($dir . "/" . $file);
            }
        }

        rmdir($dir);
    }
}