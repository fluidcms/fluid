<?php

namespace Fluid;

use PDOException, Exception, PDO;

/**
 * Init a new project
 *
 * @package fluid
 */
class Init
{
    private static $tables = array('fluid_api_consumers', 'fluid_api_nonce', 'fluid_api_tokens', 'fluid_page_tokens');

    /**
     * Check if we can run Fluid
     *
     * @return  void
     */
    public static function check()
    {
        self::checkStorage();
        self::checkGit();
        self::checkDatabase();
    }

    /**
     * Check if the storage folder exists
     *
     * @return  void
     */
    public static function checkStorage()
    {
        // Base dir
        if (!is_dir(Fluid::getConfig('storage'))) {
            mkdir(Fluid::getConfig('storage'));
        }
        // Branches
        if (!is_dir(Fluid::getConfig('storage') . "branches")) {
            mkdir(Fluid::getConfig('storage') . "branches");
        }
        // Files
        if (!is_dir(Fluid::getConfig('storage') . "files")) {
            mkdir(Fluid::getConfig('storage') . "files");
        }
        // Pages
        if (!is_dir(Fluid::getConfig('storage') . "pages")) {
            mkdir(Fluid::getConfig('storage') . "pages");
        }
        // Site
        if (!is_dir(Fluid::getConfig('storage') . "site")) {
            mkdir(Fluid::getConfig('storage') . "site");
        }
        // Structure
        if (!is_dir(Fluid::getConfig('storage') . "structure")) {
            mkdir(Fluid::getConfig('storage') . "structure");
        }
    }

    /**
     * Check if git is initalized
     *
     * @return  void
     */
    public static function checkGit()
    {
        if (!is_dir(Fluid::getConfig('storage') . '.git')) {
            Git::init();
        }
    }

    /**
     * Check if database connection is working
     *
     * @return  void
     */
    public static function checkDatabase()
    {
        try {
            $dbh = Database\Connection::getConnection();
        } catch (PDOException $e) {
            throw new Exception("Database configuration is invalid.");
        }

        $config = Fluid::getConfig('database')['config'];
        $sth = $dbh->prepare("SELECT COUNT(*) FROM `information_schema`.`tables` WHERE `table_schema`=:database AND table_name=:table;");

        foreach (self::$tables as $table) {
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
    public static function initDB(PDO $dbh)
    {
        $query = file_get_contents(__DIR__ . "/Database/Structure/Structure.sql");
        $dbh->query($query);
    }
}