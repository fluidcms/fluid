<?php

namespace Fluid\Tasks;

use Fluid\Fluid, Fluid\Git;

class CommitPush
{
    /**
     * Commit and push a branch
     *
     * @param   string  $branch
     * @param   string  $msg
     */
    public function __construct($branch, $msg)
    {
        Git::commit($branch, $msg);
        Git::push($branch);
    }

    public static function run($branch, $msg)
    {
        return new self($branch, $msg);
    }
}