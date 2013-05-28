<?php

namespace Fluid\Tasks;

use Fluid\Fluid, Fluid\Git;

class Bare
{
    /**
     * Create git repository and default branches
     */
    public function __construct()
    {
        if (!is_dir(Fluid::getConfig("storage") . "bare")) {
            self::createDir();
            self::initGit();
        }
    }

    /**
     * Create bare directory
     *
     * @return  bool
     */
    public static function createDir()
    {
        return mkdir(Fluid::getConfig("storage") . "bare");
    }

    /**
     * Init git repo
     *
     * @return  bool
     */
    public static function initGit()
    {
        return Git::initBare();
    }
}