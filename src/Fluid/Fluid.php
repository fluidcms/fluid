<?php
namespace Fluid;

use Fluid\Page\PageEntity;
use Fluid\Map\MapEntity;
use Fluid\Daemon\Daemon;
use Fluid\Session\SessionCollection;
use Fluid\Session\SessionEntity;
use Fluid\User\UserCollection;
use Psr\Log\LoggerInterface;
use Fluid\Language\LanguageEntity;

/**
 * The fluid class
 *
 * @package fluid
 */
class Fluid
{
    const VERSION = '0.1.0';

    const DEBUG_OFF = 0;
    const DEBUG_LOG = 1;

    /**
     * @var int
     */
    private $debugMode = self::DEBUG_OFF;

    /**
     * @var Request
     * @deprecated Use registry instead
     */
    private $request;

    /**
     * @var Daemon
     * @deprecated Use registry instead
     */
    private $daemon;

    /**
     * @var bool
     */
    private $isAdmin;

    /**
     * @var string
     */
    private $sessionToken;

    /**
     * @var LanguageEntity
     */
    private $language;

    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config = null)
    {
        if (null !== $config) {
            $this->setConfig($config);
        }
    }

    /**
     * Turns debug mode on
     *
     * @param int $mode
     * @return $this
     * @throws Exception\InvalidDebugModeException
     * @deprecated
     */
    public function debug($mode = self::DEBUG_LOG)
    {
        if ($mode !== self::DEBUG_LOG && $mode !== self::DEBUG_OFF) {
            throw new Exception\InvalidDebugModeException;
        }
        $this->debugMode = $mode;
        return $this;
    }

    /**
     * Get the debug mode
     *
     * @return int
     * @deprecated
     */
    public function getDebugMode()
    {
        return $this->debugMode;
    }

    /**
     * @param TemplateEngineInterface $templateEngine
     * @return $this
     */
    public function setTemplateEngine(TemplateEngineInterface $templateEngine)
    {
        $this->getRegistry()->setTemplateEngine($templateEngine);
        return $this;
    }

    /**
     * @return TemplateEngineInterface
     */
    public function getTemplateEngine()
    {
        return $this->getRegistry()->getTemplateEngine();
    }

    /**
     * @param ConfigInterface $config
     * @return $this
     */
    public function setConfig(ConfigInterface $config)
    {
        $this->getRegistry()->setConfig($config);
        return $this;
    }

    /**
     * @return ConfigInterface
     */
    public function getConfig()
    {
        return $this->getRegistry()->getConfig();
    }

    /**
     * @return MapEntity
     */
    public function getMap()
    {
        return $this->getRegistry()->getMap();
    }

    /**
     * @param MapEntity $map
     * @return $this
     */
    public function setMap(MapEntity $map)
    {
        $this->getRegistry()->setMap($map);
        return $this;
    }

    /**
     * @return MapEntity
     */
    public function map()
    {
        return $this->getRegistry()->getMap();
    }

    /**
     * @param Request $request
     * @return $this
     * @deprecated Use registry instead
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return Request
     * @deprecated Use registry instead
     */
    public function getRequest()
    {
        if (null === $this->request) {
            $this->createRequest();
        }

        return $this->request;
    }

    /**
     * @return Request
     * @deprecated Use registry instead
     */
    public function request()
    {
        return $this->getRequest();
    }

    /**
     * @return $this
     * @deprecated Use registry instead
     */
    private function createRequest()
    {
        return $this->setRequest(new Request($this));
    }

    /**
     * @return Router
     */
    public function router()
    {
        return $this->getRegistry()->getRouter();
    }

    /**
     * @param Daemon $daemon
     * @return $this
     * @deprecated Use registry instead
     */
    public function setDaemon(Daemon $daemon)
    {
        $this->daemon = $daemon;
        return $this;
    }

    /**
     * @return Daemon
     * @deprecated Use registry instead
     */
    public function getDaemon()
    {
        if (null === $this->daemon) {
            $this->createDaemon();
        }
        return $this->daemon;
    }

    /**
     * @return $this
     * @deprecated Use registry instead
     */
    private function createDaemon()
    {
        return $this->setDaemon(new Daemon($this->getConfig(), $this->getRegistry()->getStorage(), $this->getRegistry()->getXmlMappingLoader(), $this->getRegistry()->getLogger(), $this->getRegistry()->getEvent()));
    }

    /**
     * @param bool $isAdmin
     * @return $this
     */
    public function setIsAdmin($isAdmin)
    {
        $this->isAdmin = $isAdmin;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsAdmin()
    {
        if (null === $this->isAdmin) {
            $this->setIsAdmin($this->checkIfIsAdmin());
        }
        return $this->isAdmin;
    }

    /**
     * @return bool
     */
    public function isAdmin()
    {
        return $this->getIsAdmin();
    }

    /**
     * @return bool
     */
    private function checkIfIsAdmin()
    {
        if (isset($_COOKIE['fluid_session'])) {
            $session = $_COOKIE['fluid_session'];
            if (strlen($session) === SessionEntity::TOKEN_LENGHT && preg_match('/^[a-zA-Z0-9]*$/', $session)) {
                $sessions = new SessionCollection($this->getRegistry()->getStorage(), new UserCollection($this->getRegistry()->getStorage()));
                $session = $sessions->find($session);
                if ($session instanceof SessionEntity && !$session->isExpired()) {
                    $this->sessionToken = $session->getToken();
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param LanguageEntity $language
     * @return $this
     */
    public function setLanguage(LanguageEntity $language)
    {
        $this->language = $language;
        return $this;
    }

    /**
     * @return LanguageEntity
     */
    public function getLanguage()
    {
        if (null === $this->language) {
            $this->createLanguage();
        }
        return $this->language;
    }

    /**
     * @return $this
     */
    private function createLanguage()
    {
        return $this->setLanguage(new LanguageEntity($this->getConfig()->getLanguage()));
    }

    /**
     * @return RegistryInterface
     */
    public function getRegistry()
    {
        if (null === $this->registry) {
            $this->setRegistry(new Registry);
            $this->getRegistry()->setFluid($this);
        }
        return $this->registry;
    }

    /**
     * @param RegistryInterface $registry
     * @return $this
     */
    public function setRegistry(RegistryInterface $registry)
    {
        $this->registry = $registry;
        return $this;
    }

    /**
     * @return string
     */
    public function getSessionToken()
    {
        return $this->sessionToken;
    }

    /**
     * @param string $sessionToken
     * @return $this
     */
    public function setSessionToken($sessionToken)
    {
        $this->sessionToken = $sessionToken;
        return $this;
    }

    /**
     * @param $page
     * @return PageEntity
     */
    public function findPage($page)
    {
        return $this->getMap()->findPage($page);
    }
}