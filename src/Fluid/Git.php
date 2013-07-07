<?php

namespace Fluid;

/**
 * Version control for Fluid
 *
 * @package fluid
 */
class Git
{
    private static $remoteUrlChecks = array();

    /**
     * Execute a command
     *
     * @param   string  $branch
     * @param   string  $command
     * @param   bool    $check
     * @return  string
     */
    private static function command($branch, $command, $check = true)
    {
        $dir = Fluid::getConfig("storage") . $branch;
        $dir = preg_replace('!/{2,}!', '/', $dir);
        $dir = rtrim($dir, '/');

        $workTree = $dir;

        if ($branch !== 'bare') {
            $dir = $dir . "/.git";
        }

        if (!$check || is_dir($dir)) {
            $command = preg_replace("/^git/", "git --git-dir={$dir} --work-tree={$workTree}", $command);

            ob_start();
            passthru($command);
            $retval = ob_get_contents();
            ob_end_clean();

            return $retval;
        }

        return false;
    }

    /**
     * Get remotes repositories
     *
     * @param   string $branch
     * @return  array
     */
    public static function getRemotes($branch)
    {
        $retval = self::command($branch, "git remote -v");
        $remotes = array();
        foreach(explode(PHP_EOL, $retval) as $line) {
            preg_match("/([^\\s]*)(.*) (\([a-z]*\))/i", $line, $match);
            if (!empty($match[1]) && !empty($match[2])) {
                $remotes[$match[1]] = trim($match[2]);
            }
        }

        return $remotes;
    }

    /**
     * Check if remote url is a valid repository
     *
     * @param   string $url
     * @return  bool
     */
    public static function checkRemoteUrl($url)
    {
        if (isset(self::$remoteUrlChecks[$url])) {
            return self::$remoteUrlChecks[$url];
        }

        $retval = false;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);

        if (curl_exec($ch) === true) {
            $retval = true;
        }

        curl_close($ch);

        self::$remoteUrlChecks[$url] = $retval;
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

        $branches = array();

        foreach(explode(' ', $retval) as $branch) {
            if (!empty($branch)) {
                $branches[] = $branch;
            }
        }

        return $branches;
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
     * @return  bool
     */
    public static function fetchAll($branch)
    {
        $fetched = false;
        $remotes = self::getRemotes($branch);
        foreach($remotes as $remote => $url) {
            if (self::checkRemoteUrl($url)) {
                // Fetch remote
                self::command($branch, "git fetch {$remote}");
                $fetched = true;
            }
        }
        return $fetched;
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
        $remotes = self::getRemotes($localBranch);
        if (isset($remotes['origin']) && self::checkRemoteUrl($remotes['origin'])) {

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

        return false;
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
        return self::command($branch, 'git remote add origin ' . $url);
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
        return self::command($branch, 'git checkout --track ' . $remoteBranch);
    }

    /**
     * Initialize git repo
     *
     * @param   string $branch
     * @return  bool
     */
    public static function init($branch)
    {
        $retval = self::command($branch, 'git init', false);
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
     * @param   string $branch
     * @param   string $msg    The commit message
     * @param   string $name
     * @param   string $email
     * @return  string
     */
    public static function commit($branch, $msg, $name = null, $email = null)
    {
        $msg = preg_replace('/[^\s\w\-:]/', '', $msg);

        self::command($branch , 'git add -A');

        $command = "git commit -m " . escapeshellarg($msg);

        if (null !== $name && null !== $email) {
            $author = "$name <$email>";
            $command .= " --author=". escapeshellarg($author);
        }

        return self::command($branch, $command);
    }

    /**
     * Pull
     *
     * @param   string $branch
     * @param   string $remote
     * @return  string
     */
    public static function pull($branch, $remote = 'master')
    {
        return self::command($branch, "git pull origin {$remote}");
    }

    /**
     * Push
     *
     * @param   string $branch
     * @return  string
     */
    public static function push($branch)
    {
        return self::command($branch, "git push origin");
    }

    /**
     * Status
     *
     * @param   string $branch
     * @return  string
     */
    public static function status($branch)
    {
        return self::command($branch, "git status");
    }
}