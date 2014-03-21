<?php

namespace Fluid\Tasks;

use Fluid\Config;
use Fluid\Git;

class CommitPush
{
    /**
     * Commit and push a branch
     *
     * @param   string  $branch
     * @param   string  $msg
     * @return  void
     */
    public static function execute($branch, $msg)
    {
        Git::commit($branch, $msg);
        Git::push($branch);

        // Trigger update on remote server
        $gitConfig = Config::get('git');
        $url = preg_replace('!repo.git$!', 'update', $gitConfig['url']);
        file_get_contents($url);

        FetchPull::execute('master');
    }
}