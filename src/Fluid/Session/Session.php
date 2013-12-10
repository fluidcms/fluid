<?php

namespace Fluid\Session;

use Fluid\Database;
use Fluid\Token\Token;

class Session extends Database
{
    /**
     * Default timeout of 1 hour
     *
     * @var int $timeOut
     */
    private static $timeOut = 3600;

    /**
     * Create table if it doesn't exists
     */
    private static function table()
    {
        $dbh = self::getDatabase();
        if ($dbh) {
            $dbh->query("CREATE TABLE IF NOT EXISTS sessions (token CHAR(64) PRIMARY KEY, expiration DATETIME)");
        }
    }

    /**
     * Create a session
     *
     * @return string
     */
    public static function create()
    {
        self::table();

        $token = Token::generate(64);

        $dbh = self::getDatabase();

        if ($dbh) {
            $sth = $dbh->prepare("INSERT INTO sessions (token, expiration) VALUES (:token, :expiration);");

            $sth->execute(array(
                ':token' => $token,
                ':expiration' => date('Y-m-d H:i:s', time() + self::$timeOut)
            ));

            return $token;
        }

        return null;
    }

    /**
     * Validate a session
     *
     * @param string $value
     * @return bool
     */
    public static function validate($value)
    {
        self::table();

        $dbh = self::getDatabase();

        if ($dbh) {
            self::deleteExpired();

            $sth = $dbh->prepare("SELECT token FROM sessions WHERE token=:token AND expiration>:datetime;");
            $sth->execute(array(':token' => $value, ':datetime' => date('Y-m-d H:i:s')));

            if ($sth->fetch()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Delete expired sessions
     *
     * @return void
     */
    private static function deleteExpired()
    {
        $dbh = self::getDatabase();
        if ($dbh) {
            $dbh->query("DELETE FROM sessions WHERE expiration<'".date('Y-m-d H:i:s')."';");
        }
    }
}