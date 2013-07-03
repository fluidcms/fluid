<?php

namespace Fluid\Tasks;

use Fluid\Fluid, Fluid\Database\Connection, PDO, PDOException, Exception;

class Database
{
    /**
     * Create Fluid database tables if they do not exists
     *
     * @throws  Exception
     * @return  void
     */
    public static function execute()
    {
        try {
            $dbh = Connection::getConnection();
        } catch (PDOException $e) {
            throw new Exception("Database configuration is invalid.");
        }

        $config = Fluid::getConfig('database')['config'];
        $sth = $dbh->prepare("SELECT COUNT(*) FROM `information_schema`.`tables` WHERE `table_schema`=:database AND `table_name`=:table;");

        foreach (Fluid::getTables() as $table) {
            $sth->execute(array(':database' => $config["dbname"], ':table' => $table));
            $result = $sth->fetch();
            if ($result[0] != 1) {
                self::initDB($dbh);
                break;
            }
        }
    }

    /**
     * Initialize database tables
     *
     * @param   PDO $dbh
     * @return  void
     */
    public static function initDB(PDO $dbh) {
        $query = file_get_contents(__DIR__ . "/../Database/Structure.sql");
        $dbh->query($query);
    }
}