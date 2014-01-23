<?php
namespace Fluid;

use Exception;
use Fluid\Branch\Branch;
use Fluid\Socket\Message;
use Fluid\Token\Token;

/**
 * The fluid class
 * @package fluid
 */
class Fluid
{
    const VERSION = '0.0.1';
    const DEBUG_OFF = 0;
    const DEBUG_LOG = 1;
    const NOT_FOUND = '404';

    private static $branch;
    private static $storage;
    private static $language = 'en-US';
    private static $requestPayload;

    /** @var bool|null $controlPannel */
    private static $controlPannel = null;

    /** @var string $controlPannel */
    private static $controlPannelSession;

    /** @var int $debugMode */
    private static $debugMode = self::DEBUG_OFF;

    /** @var Fluid $self */
    private static $self;

    /** @var null|\Fluid\TemplateEngineInterface $templateEngine */
    private $templateEngine;

    /**
     * @param array|null $config
     * @param string|null $language The language of the instance
     */
    public function __construct(array $config = null, $language = null)
    {
        self::$self = $this;
        self::init($config, $language);
        $this->setTemplateEngine(new TemplateEngine);
    }

    /**
     * Initialize Fluid
     *
     * @param array|null $config
     * @param string|null $language The language of the instance
     * @return self
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

        self::checkIfIsAdmin();
        return null !== self::$self ? self::$self : new self;
    }

    /**
     * Turns debug mode on
     *
     * @param int $mode
     * @return self
     */
    public static function debug($mode = self::DEBUG_LOG)
    {
        self::$debugMode = $mode;
        return null !== self::$self ? self::$self : new self;
    }

    /**
     * Get the debug mode
     *
     * @return int
     */
    public static function getDebugMode()
    {
        return self::$debugMode;
    }

    /**
     * Check if fluid was loaded from the control pannel
     */
    public static function checkIfIsAdmin()
    {
        if (isset($_SERVER['QUERY_STRING'])) {
            if (!empty($_SERVER['QUERY_STRING'])) {
                parse_str($_SERVER['QUERY_STRING'], $queryString);
            } elseif (strpos($_SERVER['REQUEST_URI'], '?') !== false) {
                $parts = explode('?', $_SERVER['REQUEST_URI'], 2);
                parse_str($parts[1], $queryString);
            }
            if (
                isset($queryString['fluidbranch']) &&
                isset($queryString['fluidtoken']) &&
                isset($queryString['fluidsession']) &&
                Token::validate($queryString['fluidtoken'])
            ) {
                self::$controlPannel = true;
                self::$controlPannelSession = $queryString['fluidsession'];
                self::setBranch($queryString['fluidbranch'], true);
            }
        }
    }

    /**
     * Check if fluid was loaded from the control pannel
     *
     * @return bool
     */
    public static function isAdmin()
    {
        if (null === self::$controlPannel) {
            self::checkIfIsAdmin();
        }

        return self::$controlPannel;
    }

    /**
     * Get the control pannel user's session
     *
     * @return bool
     */
    public static function getSession()
    {
        return self::$controlPannelSession;
    }

    /**
     * Get the language of the instance
     *
     * @param string $value
     * @return self
     */
    public static function setLanguage($value)
    {
        // If page is loading from control pannel, send language to control pannel
        if (self::isAdmin()) {
            Message::send('language:changed', array(
                'session' => self::getSession(),
                'language' => $value
            ));
        }

        self::$language = $value;
        return null !== self::$self ? self::$self : new self;
    }

    /**
     * Get the language of the instance
     *
     * @return string
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
     * @param string $branch
     * @throws Exception Branch does not exists
     * @return self;
     */
    public static function setBranch($branch)
    {
        if (!Branch::exists($branch)) {
            Branch::init($branch);
        }

        if ($branch == self::$branch) {
            null;
        } else if (is_dir(Config::get('storage') . '/' . $branch)) {
            self::$branch = $branch;
        } else {
            throw new Exception("Branch does not exists.");
        }

        return null !== self::$self ? self::$self : new self;
    }

    /**
     * Get the current branch
     *
     * @return string
     */
    public static function getBranch()
    {
        return self::$branch;
    }

    /**
     * Get the current branch
     *
     * @return string
     */
    public static function getBranchStorage()
    {
        return Config::get('storage') . "/" . self::$branch;
    }

    /**
     * Set the request payload in case you use file_get_contents("php://input") before Fluid
     *
     * @param string $value
     * @return self
     */
    public static function setRequestPayload($value)
    {
        self::$requestPayload = $value;
        return null !== self::$self ? self::$self : new self;
    }

    /**
     * Get request payload
     *
     * @return string
     */
    public static function getRequestPayload()
    {
        return self::$requestPayload;
    }

    /**
     * @param null|\Fluid\TemplateEngineInterface $templateEngine
     * @return $this
     */
    public function setTemplateEngine(TemplateEngineInterface $templateEngine = null)
    {
        $this->templateEngine = $templateEngine;
        return $this;
    }

    /**
     * @return null|\Fluid\TemplateEngineInterface
     */
    public function getTemplateEngine()
    {
        return $this->templateEngine;
    }
}