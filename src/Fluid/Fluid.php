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
    private static $ready = false;
    private static $tables = array('fluid_api_consumers', 'fluid_api_nonce', 'fluid_api_tokens', 'fluid_page_tokens');
    private static $branch;
    private static $config;
    private static $storage;
    private static $language;

    const NOT_FOUND = '404';

    /**
     * Initialize Fluid
     *
     * @param   array   $config     The configuration array
     * @param   string  $language   The language of the instance
     * @return  void
     */
    public function __construct($config = null, $language = null)
    {
        self::$branch = 'master';
        self::$config = $config;
        self::$storage = $config['storage'];

        // Init Fluid
        if (!Check::check() && php_sapi_name() !== 'cli') {
            Admin\Init::init();
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

        // Check if using branch
        if (isset($_SERVER['QUERY_STRING'])) {
            parse_str($_SERVER['QUERY_STRING']);
            if (isset($fluidBranch) && isset($fluidBranchToken) && Models\PageToken::validateToken($fluidBranchToken)) {
                self::switchBranch($fluidBranch, true);
            }
        }
    }

    /**
     * Get the language of the instance
     *
     * @param   string  $branch
     * @param   bool    $create Create the branch if it does not exists
     * @throws  Exception   Branch does not exists
     * @return  void
     */
    public static function switchBranch($branch, $create = false)
    {
        if ($branch == self::$branch) {
            return;
        } else if ($branch == 'master') {
            self::setConfig('storage', self::$storage);
        } else if ($create && $branch = Git::branch($branch)) {
            self::setConfig(
                'storage',
                self::getConfig('storage') . "branches/{$branch}/"
            );
        } else {
            if (is_dir(self::getConfig('storage') . "branches/{$branch}/")) {
                self::setConfig(
                    'storage',
                    self::getConfig('storage') . "branches/{$branch}/"
                );
            } else {
                throw new Exception("Branch does not exists.");
            }
        }
        self::$branch = $branch;
    }

    /**
     * Returns the status of Fluid
     *
     * @return  bool
     */
    public static function isReady()
    {
        return self::$ready;
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
     * @return  string
     */
    public static function getTables()
    {
        return self::$tables;
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