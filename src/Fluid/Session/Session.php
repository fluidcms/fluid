<?php

namespace Fluid\Session;
use Fluid\Fluid;
use Fluid\Database;
use Fluid\Token\Token;

class Session extends Database
{
    /**
     * Default timeout of 1 hour
     */
    private static $timeOut = 3600;

    /**
     * Create a session
     *
     * @return  string
     */
    public static function create()
    {
        $token = Token::generate(64);
        $dbh = self::getDatabase();

        $dbh->query("CREATE TABLE IF NOT EXISTS sessions (token CHAR(64) PRIMARY KEY, expiration INTEGER)");
        $sth = $dbh->prepare("INSERT INTO sessions (token, expiration) VALUES (:token, :expiration);");

        $sth->execute(array(
            ':token' => $token,
            ':expiration' => date('Y-m-d H:i:s', time() + self::$timeOut)
        ));

        return $token;
    }
}