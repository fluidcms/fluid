<?php

namespace Fluid\Branch;

use Fluid\Config;
use Fluid\Fluid;
use Fluid\Git;
use Fluid\Debug\Log;

class Branch
{
    private $branch;
    private $dir;

    public function __construct($branch)
    {
        $this->branch = $branch;
        $this->dir = Config::get("storage") . $branch;
    }

    /**
     * Initialize a branch
     *
     * @param   string  $branch
     * @return  self
     */
    public static function init($branch)
    {
        Log::add('Initializing branch ' . $branch . "");
        Log::add('Checking if dir ' . Config::get("storage") . $branch . "/.git" . " exists");

        if (!is_dir(Config::get("storage") . $branch . "/.git")) {

            Log::add(Config::get("storage") . $branch . "/.git" . " does not exists");
            Log::add('Checking if dir ' . Config::get("storage") . $branch . " exists");

            if (!is_dir(Config::get("storage") . $branch)) {
                Log::add(Config::get("storage") . $branch . " does not exists");
                if (mkdir(Config::get("storage") . $branch, 0777, true)) {
                    Log::add("Created " . Config::get("storage") . $branch . "");
                } else {
                    Log::add("Failed creating " . Config::get("storage") . $branch . "");
                }
            }
            Git::init($branch);
        }

        $retval = new self($branch);

        if ($branch !== 'master') {
            $retval->pullMaster();
        } else {
            $retval->initialCommit();
        }

        return $retval;
    }

    /**
     * Merge commits from master branch
     *
     * @return  void
     */
    public function pullMaster()
    {
        if (!self::exists('master')) {
            $master = self::init('master');
        } else {
            $master = new self('master');
        }

        Git::addRemote($this->branch, $master->getDir());
        Log::add('Branch ' . $this->branch . ' pulls from master');
        Git::pull($this->branch);
        Git::clean($this->branch);
    }

    /**
     * Get repo directory
     *
     * @return  string
     */
    public function getDir()
    {
        return $this->dir;
    }

    /**
     * Check if a branch exists
     *
     * @param   string  $branch
     * @return  bool
     */
    public static function exists($branch)
    {
        return is_dir(Config::get('storage') . $branch . '/.git');
    }

    /**
     * Initial commit
     *
     * @return  void
     */
    public function initialCommit()
    {
        $gitIgnoreFile = Config::get("storage") . $this->branch . "/.gitignore";
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
        Log::add('Added '.$gitIgnoreFile.' file for initial commit on '.$this->branch.' branch');
        Git::commit($this->branch, 'initial commit');
    }
}