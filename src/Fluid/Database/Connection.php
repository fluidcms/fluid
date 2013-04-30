<?php namespace Fluid\Database;

use PDO, Fluid;

class Connection
{
    private static $connection;

    /**
     * Get the database connection. Initialize the connection if it is not already done.
     *
     * @return  PDO
     */
    public static function getConnection()
    {
        if (!isset(self::$connection)) {
            self::connect();
        }

        return self::$connection;
    }

    /**
     * Connect to the database.
     *
     * @return  void
     */
    private static function connect()
    {
        $config = Fluid\Fluid::getConfig('database')['config'];

        $dsn = 'mysql:dbname=' . $config['dbname'] . ';host=' . $config['host'];
        $dbh = new PDO($dsn, $config['user'], $config['password']);

        self::$connection = $dbh;
    }
}