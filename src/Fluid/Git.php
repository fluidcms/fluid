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
     * Initialize Git repo.
     *
     * @return  void
     */
    public static function init()
    {
        chdir(Fluid::getConfig('storage'));
        $retval = exec('git init');

        if (stristr($retval, 'Initialized') !== false) {
            file_put_contents(Fluid::getConfig('storage') . ".gitignore", ".DS_Store\n._*\n.Spotlight-V100\n.Trashes\nThumbs.db\nDesktop.ini\nbranches/*");
            self::commit('fluid');
        }
    }

    /**
     * Commit.
     *
     * @param   string  $msg    The commit message
     * @return  void
     */
    public static function commit($msg)
    {
        $msg = preg_replace('/[^a-zA-Z0-9\.\'", \-]/', '', $msg);
        chdir(Fluid::getConfig('storage'));
        exec("git add .");
        exec("git commit -m '{$msg}'");
    }

    /**
     * Branch.
     *
     * @param   string  $branchName The branch name
     * @return  string  Branch name
     */
    public static function branch($branchName)
    {
        $branchName = preg_replace('/[^a-zA-Z0-9]/', '', $branchName);
        if (!is_dir(Fluid::getConfig('storage') . "branches/{$branchName}/.git")) {
            chdir(Fluid::getConfig('storage').'/branches/');
            $retval = exec("git clone --no-hardlinks ../ {$branchName}");
            if (stristr($retval, 'Initialized') !== false) {
                chdir(Fluid::getConfig('storage')."/branches/{$branchName}");
                echo exec("git branch {$branchName}");
                echo exec("git checkout {$branchName}");
                return $branchName;
            } else {
                return false;
            }
        } else {
            return $branchName;
        }
    }
}