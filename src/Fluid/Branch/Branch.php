<?php

namespace Fluid\Branch;
use Fluid\Fluid;
use Fluid\Git;

class Branch
{
    private $branch;
    private $dir;

    public function __construct($branch)
    {
        $this->branch = $branch;
        $this->dir = Fluid::getConfig("storage") . $branch;
    }

    /**
     * Initialize a branch
     *
     * @param   string  $branch
     * @return  self
     */
    public static function init($branch)
    {
        if (!is_dir(Fluid::getConfig("storage") . $branch . "/.git")) {
            if (!is_dir(Fluid::getConfig("storage") . $branch)) {
                mkdir(Fluid::getConfig("storage") . $branch);
            }
            Git::init($branch);
        }

        $retval = new self($branch);

        if ($branch !== 'master') {
            $retval->pullMaster();
        }
//        self::checkout($branch, $tracking);
//        self::intialCommit($branch);

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
        return is_dir(Fluid::getConfig('storage') . $branch . '/.git');
    }
}