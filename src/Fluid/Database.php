<?php

namespace Fluid;
use PDO;

class Database
{
    /**
     * Return the database object
     *
     * @return  PDO
     */
    protected static function getDatabase()
    {
        $database = Fluid::getConfig('storage') . "data";

        if (!file_exists(dirname($database))) {
            mkdir(dirname($database));
        }

        return new PDO('sqlite:'.$database);
    }
}
