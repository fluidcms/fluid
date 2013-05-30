<?php

namespace Fluid\Tasks;

use Fluid\Fluid, Fluid\Git;

class Fetch
{
    /**
     * Fetch from origin
     *
     * @param   string  $branch
     * @return  void
     */
    public static function execute($branch)
    {
        Git::fetchAll($branch);
    }
}