<?php

namespace Fluid\Tests;

use ZipArchive;

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

    public static function copyStorage($dir = null, $dest = null)
    {
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
                self::copyStorage($dir . "/" . $file, $dest . "/" . $file);
            }
        }

        exec("git init ". __DIR__ . "/Fluid/Tests/Fixtures/storage/develop");
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