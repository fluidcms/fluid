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

    public static function getStorage()
    {
        return __DIR__ . "/Fluid/Tests/Fixtures/storage/develop";
    }

    public static function copyStorage()
    {
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