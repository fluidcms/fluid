<?php

namespace Fluid\History;

use Fluid\Token\Token,
    Fluid\Git,
    Fluid\Fluid;

class History
{
    private $branch;

    /**
     * Init
     */
    public function __construct()
    {
        $this->branch = Fluid::getBranch();
    }

    /**
     * Get all history steps
     *
     * @return  array
     */
    public function getAll()
    {
        $ghostCommits = array();
        $head = Git::getHeadBranch($this->branch);

        // Get master branch commits
        if ($head !== 'master') {
            $ghostCommits = Git::getCommits($this->branch, 'master');
            $ghostCommits = array_map(function($commit) { return array_merge($commit, array('ghost' => true)); }, $ghostCommits);
        }

        $output = array();
        foreach(array_merge($ghostCommits, Git::getCommits($this->branch)) as $commit) {
            if (strpos($commit['message'], 'history') === 0) {
                $output[$commit['commit']] = array(
                    'id' => $commit['commit'],
                    'action' => preg_replace('/history \d{4,4}-\d{2,2}-\d{2,2} \d{2,2}:\d{2,2}:\d{2,2} (.*) [a-zA-Z0-9]{16,16}/', '$1', $commit['message']),
                    'user_name' => preg_replace('/(.*) <(.*)>$/', '$1', $commit['author']),
                    'user_email' => preg_replace('/(.*) <(.*)>$/', '$2', $commit['author']),
                    'date' => $commit['date'],
                    'ghost' => (isset($commit['ghost']) ? true : false)
                );
            }
        }

        return array_values(array_reverse($output));
    }

    /**
     * Roll back to a commit
     *
     * @param   string  $id
     * @return  self
     */
    public static function rollBack($id)
    {
        $branch = Fluid::getBranch();
        $head = Git::getHeadBranch($branch);

        if ($head !== 'master') {
            Git::checkout($branch, 'master');
            Git::removeBranch($branch, 'history');
        }

        Git::checkoutCommit($branch, 'history', $id);

        return new self;
    }

    /**
     * Commit a step in history
     *
     * @param   string  $msg
     * @param   string  $name
     * @param   string  $email
     * @return  void
     */
    public static function add($msg, $name, $email)
    {
        Git::commit(
            Fluid::getBranch(),
            'history ' . date('Y-m-d H:i:s') . ' ' . $msg . ' ' . Token::generate(16),
            $name,
            $email
        );
    }
}