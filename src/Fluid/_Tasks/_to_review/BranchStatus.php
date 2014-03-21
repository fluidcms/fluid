<?php

namespace Fluid\Tasks;

use Fluid\Fluid, Fluid\Git, Fluid\MessageQueue;

class BranchStatus
{
    /**
     * Check the status of a branch
     *
     * @param   string  $branch
     * @return  string
     */
    public static function execute($branch)
    {
        $status = Git::status($branch);

        if (preg_match("/branch is behind '(.*)' by (\d*)/", $status, $match)) {
            $data = array('status' => 'behind', 'amount' => $match[2]);
        } else if (strpos($status, "nothing to commit") === false) {
            $data = array('status' => 'ahead');
        } else {
            $data = array('status' => 'nothing');
        }

        MessageQueue::send(array(
            'task' => 'WatchBranchStatus',
            'data' => array(
                'branch' => $branch,
                'message' => array(
                    'target' => 'version',
                    'data' => $data
                )
            )
        ));

        return $data;
    }
}