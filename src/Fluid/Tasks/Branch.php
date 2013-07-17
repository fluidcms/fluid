<?php

namespace Fluid\Tasks;

use Fluid\Fluid, Fluid\Git;

class Branch
{
    /**
     * Create git repository and default branches
     *
     * @param   string  $branch
     * @param   string  $tracking
     * @return  void
     */
    public static function execute($branch, $tracking = 'master')
    {
        if (!is_dir(Fluid::getConfig("storage") . $branch . "/.git")) {
            self::createDir($branch);
            self::initGit($branch);
        }
        self::fetchRemote($branch);
        self::checkout($branch, $tracking);
        self::intialCommit($branch);
    }

    /**
     * Create bare directory
     *
     * @param   string  $branch
     * @return  bool
     */
    public static function createDir($branch)
    {
        if (!is_dir(Fluid::getConfig("storage") . $branch)) {
            return mkdir(Fluid::getConfig("storage") . $branch);
        }
        return true;
    }

    /**
     * Init git repo
     *
     * @param   string  $branch
     * @return  bool
     */
    public static function initGit($branch)
    {
        if (Git::init($branch)) {
            $gitConfig = Fluid::getConfig('git');
            Git::addRemote($branch, $gitConfig['url'], $gitConfig['username'], $gitConfig['password']);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Init git repo
     *
     * @param   string  $branch
     * @return  void
     */
    public static function fetchRemote($branch)
    {
        Git::fetchAll($branch);
    }

    /**
     * Checkout branch
     *
     * @param   string  $branch
     * @param   string  $tracking
     * @return  void
     */
    public static function checkout($branch, $tracking)
    {
        if (!Git::isTracking($branch, $tracking)) {
            $remoteBranches = Git::getRemoteBranches($branch);

            foreach($remoteBranches as $remoteBranch) {
                if (strpos($remoteBranch, $tracking) == (strlen($remoteBranch) - strlen($tracking))) {
                    Git::checkoutRemote($branch, $remoteBranch);
                }
            }
        }
    }

    /**
     * Initial commit
     *
     * @param   string  $branch
     * @return  void
     */
    public static function intialCommit($branch)
    {
        if ($branch !== 'bare') {
            $gitIgnoreFile = Fluid::getConfig("storage") . $branch . "/.gitignore";
            $gitIgnoreContent = <<<TEXT
.DS_Store
._*
.Spotlight-V100
.Trashes
Thumbs.db
Desktop.ini
/cache/*
TEXT;
            file_put_contents($gitIgnoreFile, $gitIgnoreContent);
            Git::commit($branch, 'initial commit');
        }
    }
}