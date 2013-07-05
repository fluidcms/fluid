<?php

namespace Fluid;

use Exception;

/**
 * The fluid class
 *
 * @package fluid
 */
class Fluid
{
    private static $tables = array('fluid_api_consumers', 'fluid_api_nonce', 'fluid_api_tokens', 'fluid_page_tokens');
    private static $branch;
    private static $config;
    private static $storage;
    private static $language = 'en-US';
    private static $requestPayload;

    const NOT_FOUND = '404';

    /**
     * Initialize Fluid
     *
     * @param   array   $config     The configuration array
     * @param   string  $language   The language of the instance
     */
    public function __construct($config = null, $language = null)
    {
        self::$branch = 'master';
        self::config($config);

        // Init Fluid
        if (!VerifyFluid::check() && php_sapi_name() !== 'cli') {
            Init::init();
        }

        // Set language
        if (null === $language) {
            $languages = self::getConfig('languages');
            $language = reset($languages);
        }
        self::$language = $language;

        // Set View Templates Directory
        if (null === View::getTemplatesDir()) {
            View::setTemplatesDir(self::getConfig('templates'));
        }

        // Validate token and change branch
        if (isset($_SERVER['QUERY_STRING'])) {
            parse_str($_SERVER['QUERY_STRING'], $queryString);
            if (isset($queryString['fluidBranch']) && isset($queryString['fluidToken']) && Token::validateToken($queryString['fluidToken'])) {
                self::setBranch($queryString['fluidBranch'], true);
            }
        }
    }

    /**
     * Configure Fluid
     *
     * @param   array   $config     The configuration array
     * @return  void
     */
    public static function config($config = null)
    {
        self::$config = $config;
        self::$storage = $config['storage'];
    }

    /**
     * Get the language of the instance
     *
     * @param   string  $value
     * @return  string
     */
    public static function setLanguage($value)
    {
        self::$language = $value;
    }

    /**
     * Get the language of the instance
     *
     * @return  string
     */
    public static function getLanguage()
    {
        return self::$language;
    }

    /**
     * Get the fluid database tables
     *
     * @return  array
     */
    public static function getTables()
    {
        return self::$tables;
    }

    /**
     * Set the branch
     *
     * @param   string  $branch
     * @param   bool    $create
     * @throws  Exception   Branch does not exists
     * @return  void
     */
    public static function setBranch($branch, $create = false)
    {
        if ($create && !VerifyFluid::branchExists($branch)) {
            Tasks\Branch::execute($branch);
        }

        if ($branch == self::$branch) {
            return null;
        } else if (is_dir(self::getConfig('storage') . $branch)) {
            self::$branch = $branch;
        } else {
            throw new Exception("Branch does not exists.");
        }
    }

    /**
     * Get the current branch
     *
     * @return  string
     */
    public static function getBranch()
    {
        return self::$branch;
    }

    /**
     * Get the current branch
     *
     * @return  string
     */
    public static function getBranchStorage()
    {
        return self::getConfig('storage') . self::$branch . "/";
    }

    /**
     * Set the request payload in case you use file_get_contents("php://input") before Fluid
     *
     * @param   string  $value
     * @return  void
     */
    public static function setRequestPayload($value)
    {
        self::$requestPayload = $value;
    }

    /**
     * Get request payload
     *
     * @return  string
     */
    public static function getRequestPayload()
    {
        return self::$requestPayload;
    }

    /**
     * Get a configuration
     *
     * @param   string  $name     The key of the config
     * @return  mixed
     */
    public static function getConfig($name)
    {
        switch ($name) {
            case 'storage':
            case 'templates':
            case 'layouts':
                if (isset(self::$config[$name])) {
                    return (substr(self::$config[$name], -1) === '/' ? self::$config[$name] : self::$config[$name] . '/');
                }
                break;
            case 'url':
            case 'database':
            case 'languages':
            case 'git':
            case 'ports':
                if (isset(self::$config[$name])) {
                    return self::$config[$name];
                }
                break;
        }

        return null;
    }

    /**
     * Set a configuration
     *
     * @param   string  $name     The key of the config
     * @param   string  $value    The value of the config
     * @return  void
     */
    public static function setConfig($name, $value)
    {
        switch ($name) {
            case 'url':
            case 'storage':
            case 'templates':
            case 'database':
            case 'layouts':
            case 'git':
            case 'ports':
                self::$config[$name] = $value;
                break;
            case 'languages':
                self::$config[$name] = $value;
                if (null === self::$language) {
                    self::$language = reset($value);
                }
                break;
        }
    }
}