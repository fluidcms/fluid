<?php

namespace Fluid\Tasks;

use Fluid\Fluid, Fluid\Git;

class Branch
{
    /**
     * Create git repository and default branches
     *
     * @param   string  $branch
     */
    public function __construct($branch, $tracking = 'master')
    {
        if (!is_dir(Fluid::getConfig("storage") . $branch . "/.git")) {
            self::createDir($branch);
            self::initGit($branch);
        }
        self::fetchRemote($branch);
        self::checkout($branch, $tracking);
    }

    /**
     * Create bare directory
     *
     * @param   string  $branch
     * @return  bool
     */
    public static function createDir($branch)
    {
        return mkdir(Fluid::getConfig("storage") . $branch);
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
}