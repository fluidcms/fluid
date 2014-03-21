<?php

namespace Fluid;

use PDO;

class Database
{
    private static $dbh;

    /**
     * Return the database object
     *
     * @return PDO
     */
    protected static function getDatabase()
    {
        if (null !== self::$dbh) {
            return self::$dbh;
        }

        $storage = Config::get('storage');

        if ($storage) {
            if (!is_dir(dirname($storage))) {
                mkdir(dirname($storage), 0777, true);
            }

            $database = realpath($storage) . DIRECTORY_SEPARATOR . 'data';

            return self::$dbh = new PDO('sqlite:' . $database);
        }

        return null;
    }
}
