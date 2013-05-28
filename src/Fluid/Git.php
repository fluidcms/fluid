<?php

namespace Fluid;

/**
 * Version control for Fluid
 *
 * @package fluid
 */
class Git
{
    /**
     * Execute a command
     *
     * @param   string $branch
     * @param   string $command
     * @return  string
     */
    private static function command($branch, $command)
    {
        chdir(Fluid::getConfig("storage") . $branch);
        ob_start();
        passthru($command);
        $retval = ob_get_contents();
        ob_end_clean();

        return $retval;
    }

    /**
     * Get remote branches
     *
     * @param   string $branch
     * @return  array
     */
    public static function getRemoteBranches($branch)
    {
        $retval = self::command($branch, 'git branch -r');
        $retval = preg_replace('/\s{2,}/', ' ', trim($retval));
        return explode(' ', $retval);
    }

    /**
     * Get remote branches
     *
     * @param   string $branch
     * @return  array
     */
    public static function getBranches($branch)
    {
        $retval = self::command($branch, 'git branch');
        $retval = preg_replace('/\s{2,}/', ' ', trim($retval));
        return explode(' ', $retval);
    }

    /**
     * Fetch all remote branches
     *
     * @param   string $branch
     * @return  string
     */
    public static function fetchAll($branch)
    {
        return self::command($branch, 'git fetch --all');
    }

    /**
     * Check if local branch is tracking remote branch
     *
     * @param   string $localBranch
     * @param   string $remoteBranch
     * @return  bool
     */
    public static function isTracking($localBranch, $remoteBranch)
    {
        $retval = self::command($localBranch, 'git remote show origin');

        if (
            preg_match("/{$localBranch} merges with remote {$remoteBranch}/i", $retval) &&
            preg_match("/{$localBranch} pushes to {$remoteBranch}/i", $retval)
        ) {
            $tracking = true;
        } else {
            $tracking = false;
        }

        return $tracking;
    }

    /**
     * Add remote repo to branch
     *
     * @param   string $branch
     * @param   string $url
     * @param   string $username
     * @param   string $password
     * @return  string
     */
    public static function addRemote($branch, $url, $username, $password)
    {
        $url = preg_replace("{^(https?://)(.*)$}i", "$1{$username}:{$password}@$2", $url); // TODO this is not secure
        return self::command($branch, 'git remote add origin '.$url);
    }

    /**
     * Add remote repo to branch
     *
     * @param   string $branch
     * @param   string $remoteBranch
     * @return  string
     */
    public static function checkoutRemote($branch, $remoteBranch)
    {
        return self::command($branch, 'git checkout --track '.$remoteBranch);
    }

    /**
     * Initialize git repo
     *
     * @param   string $branch
     * @return  bool
     */
    public static function init($branch)
    {
        $retval = self::command($branch, 'git init');
        if (stristr($retval, 'Initialized') !== false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Initialize bare git repo
     *
     * @return  bool
     */
    public static function initBare()
    {
        $retval = self::command('bare', 'git init --bare');
        if (stristr($retval, 'Initialized') !== false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Commit.
     *
     * @param   string $msg    The commit message
     * @return  void
     */
//    public static function commit($msg)
//    {
//        $msg = preg_replace('/[^a-zA-Z0-9\.\'", \-]/', '', $msg);
//        chdir(Fluid::getConfig('storage'));
//        exec("git add .");
//        exec("git commit -m '{$msg}'");
//    }

    /**
     * Branch.
     *
     * @param   string $branchName The branch name
     * @return  string  Branch name
     */
//    public static function branch($branchName)
//    {
//        $branchName = preg_replace('/[^a-zA-Z0-9]/', '', $branchName);
//        if (!is_dir(Fluid::getConfig('storage') . "branches/{$branchName}/.git")) {
//            chdir(Fluid::getConfig('storage').'/branches/');
//            $retval = exec("git clone --no-hardlinks ../ {$branchName}");
//            if (stristr($retval, 'Initialized') !== false) {
//                chdir(Fluid::getConfig('storage')."/branches/{$branchName}");
//                echo exec("git branch {$branchName}");
//                echo exec("git checkout {$branchName}");
//                return $branchName;
//            } else {
//                return false;
//            }
//        } else {
//            return $branchName;
//        }
//    }
}