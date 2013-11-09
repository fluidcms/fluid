<?php

namespace Fluid\Token;

use Fluid\Fluid;
use Fluid\Database;

class Token extends Database
{
    /**
     * Default timeout of 1 hour
     *
     * @var int $timeOut
     */
    private static $timeOut = 3600;

    /**
     * Get a token
     *
     * @return string
     */
    public static function get()
    {
        return self::create();
    }

    /**
     * Create table if it doesn't exists
     */
    private static function table()
    {
        $dbh = self::getDatabase();
        $dbh->query("CREATE TABLE IF NOT EXISTS page_tokens (token CHAR(64) PRIMARY KEY, expiration DATETIME)");
    }

    /**
     * Create a token
     *
     * @return string
     */
    public static function create()
    {
        self::table();

        $token = self::generate(64);

        $dbh = self::getDatabase();

        $sth = $dbh->prepare("INSERT INTO page_tokens (token, expiration) VALUES (:token, :expiration);");

        $sth->execute(array(
            ':token' => $token,
            ':expiration' => date('Y-m-d H:i:s', time() + self::$timeOut)
        ));
        return $token;
    }

    /**
     * Validate a token and delete it
     *
     * @param string $value
     * @return bool
     */
    public static function validate($value)
    {
        self::table();

        $dbh = self::getDatabase();

        self::deleteExpired();

        $sth = $dbh->prepare("SELECT token FROM page_tokens WHERE token=:token AND expiration>date('now');");
        $sth->execute(array(':token' => $value));

        if ($sth->fetch()) {
            // TODO: Delete token?
            return true;
        }
        return false;
    }

    /**
     * Delete expired tokens
     */
    private static function deleteExpired()
    {
        $dbh = self::getDatabase();
        $dbh->query("DELETE FROM page_tokens WHERE expiration<date('now');");
    }

    /**
     * Generate a random string
     *
     * @param int
     * @return string
     */
    public static function generate($length)
    {
        $characters = array(
            'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'o', 'j', 'k', 'l',
            'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D',
            'E', 'F', 'G', 'H', 'O', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U',
            'V', 'W', 'X', 'Y', 'Z', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'
        );

        srand((float)microtime() * 1000000);

        $token = '';

        do {
            shuffle($characters);
            $token .= $characters[mt_rand(0, (count($characters) - 1))];
        } while (strlen($token) < $length);

        return $token;
    }
}