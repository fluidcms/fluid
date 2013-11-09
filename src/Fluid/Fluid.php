<?php

namespace Fluid;

use Exception;
use Fluid\Branch\Branch;

/**
 * The fluid class
 *
 * @package fluid
 */
class Fluid
{
    const DEBUG_OFF = 0;
    const DEBUG_LOG = 1;

    const NOT_FOUND = '404';

    private static $branch;
    private static $storage;
    private static $language = 'en-US';
    private static $requestPayload;
    private static $requestFromControlPannel = false;

    private static $debugMode = self::DEBUG_OFF;

    /**
     * Initialize Fluid
     *
     * @param array|null $config
     * @param string|null $language The language of the instance
     */
    public static function init(array $config = null, $language = null)
    {
        if (null !== $config) {
            Config::setAll($config);
        }
        self::$branch = 'master';
        self::$storage = Config::get('storage');
        if (null !== $language) {
            self::$language = $language;
        }

        // Validate token and change branch
        if (isset($_SERVER['QUERY_STRING'])) {
            parse_str($_SERVER['QUERY_STRING'], $queryString);
            if (isset($queryString['fluidbranch']) && isset($queryString['fluidtoken']) && Token\Token::validate($queryString['fluidtoken'])) {
                self::$requestFromControlPannel = $queryString['fluidsession'];
                self::setBranch($queryString['fluidbranch'], true);
            }
        }

        // Set View Templates Directory
        if (null === View::getTemplatesDir()) {
            View::setTemplatesDir(Config::get('templates'));
        }
    }

    /**
     * Turns debug mode on
     *
     * @param   int $mode
     * @return  void
     */
    public static function debug($mode = self::DEBUG_LOG)
    {
        self::$debugMode = $mode;
    }

    /**
     * Get the debug mode
     *
     * @return  int
     */
    public static function getDebugMode()
    {
        return self::$debugMode;
    }

    /**
     * Get the language of the instance
     *
     * @param   string $value
     * @return  string
     */
    public static function setLanguage($value)
    {
        // If page is loading from control pannel, send language to control pannel
        if (self::$requestFromControlPannel) {
            MessageQueue::send(array(
                'task' => 'LanguageDetected',
                'data' => array(
                    'session' => self::$requestFromControlPannel,
                    'message' => array(
                        'target' => 'language_detected',
                        'data' => array(
                            'language' => $value
                        )
                    )
                )
            ));
        }

        self::$language = $value;
    }

    /**
     * Get the language of the instance
     *
     * @return  string
     */
    public static function getLanguage()
    {
        // Set language
        if (null === self::$language) {
            $languages = Config::get('languages');
            $language = reset($languages);
            self::setLanguage($language);
        }

        return self::$language;
    }

    /**
     * Set the branch
     *
     * @param   string $branch
     * @throws  Exception   Branch does not exists
     * @return  void
     */
    public static function setBranch($branch)
    {
        if (!Branch::exists($branch)) {
            Branch::init($branch);
        }

        if ($branch == self::$branch) {
            return null;
        } else if (is_dir(Config::get('storage') . '/' . $branch)) {
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
        return Config::get('storage') . "/" . self::$branch;
    }

    /**
     * Set the request payload in case you use file_get_contents("php://input") before Fluid
     *
     * @param   string $value
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
}