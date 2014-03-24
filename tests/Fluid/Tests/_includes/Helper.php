<?php
namespace Fluid\Tests;

class Helper
{
    public static function init()
    {
        self::destroy();
    }

    public static function destroy()
    {
        if (is_dir(self::getStorage() . "/develop")) {
            self::deleteDir(self::getStorage() . "/develop");
        }
        if (is_dir(self::getStorage() . "/master")) {
            self::deleteDir(self::getStorage() . "/master");
        }
        if (file_exists(self::getStorage() . "/data")) {
            self::deleteDir(self::getStorage() . "/data");
        }
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
        return realpath(__DIR__ . "/../_files");
    }

    public static function getStorage($dir = null)
    {
        return sys_get_temp_dir() . "/FluidUnitTests/storage" . ($dir !== null ? "/{$dir}" : null);
    }

    public static function copyDir($dir, $dest)
    {
        if (!is_dir($dest)) {
            mkdir($dest, 0777, true);
        }

        foreach (scandir($dir) as $file) {
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
        foreach (scandir($dir) as $file) {
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

    public static function createData()
    {
        if (is_dir(self::getStorage() . "/data")) {
            self::deleteDir(self::getStorage() . "/data");
        }

        self::copyDir(
            self::getFixtureDir() . "/data",
            self::getStorage() . "/data"
        );
    }

    public static function createDevelop()
    {
        if (is_dir(self::getStorage() . "/develop")) {
            self::deleteDir(self::getStorage() . "/develop");
        }

        self::createMaster();

        self::copyDir(
            self::getStorage() . "/master",
            self::getStorage() . "/develop"
        );

        $dir = self::getStorage() . "/develop";

        exec("git init " . $dir);
        exec("git --git-dir=" . $dir . "/.git --work-tree=" . $dir . " add " . $dir . "/*");
        exec("git --git-dir=" . $dir . "/.git --work-tree=" . $dir . " commit -m initial\\ commit");
    }

    public static function createMaster()
    {
        if (is_dir(self::getStorage() . "/master")) {
            self::deleteDir(self::getStorage() . "/master");
        }

        self::copyDir(
            self::getFixtureDir() . "/storage",
            self::getStorage() . "/master"
        );
    }

    public static function commitMaster()
    {
        $dir = self::getStorage() . "/master";

        exec("git init " . $dir);
        exec("git --git-dir=" . $dir . "/.git --work-tree=" . $dir . " add " . $dir . "/*");
        exec("git --git-dir=" . $dir . "/.git --work-tree=" . $dir . " commit -m initial\\ commit");
    }
}