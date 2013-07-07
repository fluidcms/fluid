<?php

namespace Fluid;

use Fluid\Token\Token;

class History
{
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