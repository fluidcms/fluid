<?php

namespace Fluid;
use PDO;

class Database
{
    private static $dbh;

    /**
     * Return the database object
     *
     * @return  PDO
     */
    protected static function getDatabase()
    {
        if (null !== self::$dbh) {
            return self::$dbh;
        }

        $database = Fluid::getConfig('storage') . "data";

        if (!is_dir(dirname($database))) {
            mkdir(dirname($database), 0777, true);
        }

        return self::$dbh = new PDO('sqlite:'.$database);
    }
}
