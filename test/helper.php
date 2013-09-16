<?php

namespace Fluid\Tests;

use Fluid\Fluid;

class Helper
{
    public static function init()
    {
        if (is_dir(__DIR__ . "/Fluid/Tests/Fixtures/storage/develop")) {
            self::deleteDir(__DIR__ . "/Fluid/Tests/Fixtures/storage/develop");
        }
        if (is_dir(__DIR__ . "/Fluid/Tests/Fixtures/storage/master")) {
            self::deleteDir(__DIR__ . "/Fluid/Tests/Fixtures/storage/master");
        }
        if (file_exists(__DIR__ . "/Fluid/Tests/Fixtures/storage/data")) {
            unlink(__DIR__ . "/Fluid/Tests/Fixtures/storage/data");
        }
    }

    public static function destroy()
    {
        self::init();
    }

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

    public static function copyDir($dir, $dest)
    {
        if (!is_dir($dest)) {
            mkdir($dest);
        }

        foreach(scandir($dir) as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            } else if (is_file($dir . "/" . $file)) {
                copy($dir . "/" . $file, $dest . "/" . $file);
            } else if (is_dir($dir . "/" . $file)) {
                self::copyDir($dir . "/" . $file, $dest . "/" . $file);
            }
        }
    }

    public static function deleteDir($dir)
    {
        foreach(scandir($dir) as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            } else if (is_file($dir . "/" . $file)) {
                unlink($dir . "/" . $file);
            } else if (is_dir($dir . "/" . $file)) {
                self::deleteDir($dir . "/" . $file);
            }
        }

        rmdir($dir);
    }

    public static function createDevelop()
    {
        if (is_dir(__DIR__ . "/Fluid/Tests/Fixtures/storage/develop")) {
            self::deleteDir(__DIR__ . "/Fluid/Tests/Fixtures/storage/develop");
        }

        self::createMaster();

        self::copyDir(
            __DIR__ . "/Fluid/Tests/Fixtures/storage/master",
            __DIR__ . "/Fluid/Tests/Fixtures/storage/develop"
        );

        exec("git init ". self::getStorage());
        exec("git --git-dir=".self::getStorage()."/.git --work-tree=".self::getStorage()." add ".self::getStorage()."/*");
        exec("git --git-dir=".self::getStorage()."/.git --work-tree=".self::getStorage()." commit -m initial\\ commit");

        Fluid::setBranch('develop');
    }

    public static function createMaster()
    {
        if (is_dir(__DIR__ . "/Fluid/Tests/Fixtures/storage/master")) {
            self::deleteDir(__DIR__ . "/Fluid/Tests/Fixtures/storage/master");
        }

        self::copyDir(
            __DIR__ . "/Fluid/Tests/Fixtures/storage/default",
            __DIR__ . "/Fluid/Tests/Fixtures/storage/master"
        );
    }

    public static function commitMaster()
    {
        $dir = __DIR__ . "/Fluid/Tests/Fixtures/storage/master";

        exec("git init ". $dir);
        exec("git --git-dir=".$dir."/.git --work-tree=".$dir." add ".$dir."/*");
        exec("git --git-dir=".$dir."/.git --work-tree=".$dir." commit -m initial\\ commit");
    }
}