<?php

namespace Fluid\Tasks;

use Fluid\Fluid, Fluid\Git;

class FetchPull
{
    /**
     * Fetch and pull from origin
     *
     * @param   string  $branch
     * @param   string  $remote
     * @return  void
     */
    public static function execute($branch, $remote = 'master')
    {
        Git::fetchAll($branch);
        Git::pull($branch, $remote);
    }
}