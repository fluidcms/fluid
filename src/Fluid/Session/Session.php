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
     * Create table if it doesn't exists
     *
     * @return  void
     */
    private static function table() {
        $dbh = self::getDatabase();
        $dbh->query("CREATE TABLE IF NOT EXISTS sessions (token CHAR(64) PRIMARY KEY, expiration DATETIME)");
    }

    /**
     * Create a session
     *
     * @return  string
     */
    public static function create()
    {
        self::table();

        $token = Token::generate(64);

        $dbh = self::getDatabase();

        $sth = $dbh->prepare("INSERT INTO sessions (token, expiration) VALUES (:token, :expiration);");

        $sth->execute(array(
            ':token' => $token,
            ':expiration' => date('Y-m-d H:i:s', time() + self::$timeOut)
        ));

        return $token;
    }

    /**
     * Validate a session
     *
     * @param   string  $value
     * @return  bool
     */
    public static function validate($value)
    {
        self::table();

        $dbh = self::getDatabase();

        self::deleteExpired();

        $sth = $dbh->prepare("SELECT token FROM sessions WHERE token=:token AND expiration>date('now');");
        $sth->execute(array(':token' => $value));

        if ($sth->fetch()) {
            return true;
        }
        return false;
    }

    /**
     * Delete expired sessions
     *
     * @return  void
     */
    private static function deleteExpired()
    {
        $dbh = self::getDatabase();
        $dbh->query("DELETE FROM sessions WHERE expiration<date('now');");
    }
}