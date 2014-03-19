<?php
namespace Fluid\Session;

use DateTime;
use DateInterval;
use Fluid\Token;
use Fluid\User\UserEntity;

class SessionEntity
{
    const COOKIE_NAME = 'fluid_session';
    const EXPIRATION_TIME = 'PT1H';

    /**
     * @var string
     */
    private $token;

    /**
     * @var UserEntity
     */
    private $user;

    /**
     * @var bool
     */
    private $isLongSession = false;

    /**
     * @var \DateTime|null
     */
    private $expirationDate = null;

    public function __construct()
    {
        $this->createToken();
        $this->createExpirationDate();
    }

    /**
     * @param \DateTime|null $expirationDate
     * @return $this
     */
    public function setExpirationDate(DateTime $expirationDate = null)
    {
        $this->expirationDate = $expirationDate;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getExpirationDate()
    {
        return $this->expirationDate;
    }

    /**
     * @return $this
     */
    public function createExpirationDate()
    {
        $expiration = new DateTime('now');
        $expiration->add(new DateInterval(self::EXPIRATION_TIME));
        return $this->setExpirationDate($expiration);
    }

    /**
     * @return bool
     */
    public function isExpired()
    {
        if ($this->getExpirationDate() < new DateTime("now")) {
            return true;
        }
        return false;
    }

    /**
     * @param bool $isLongSession
     * @return $this
     */
    public function setIsLongSession($isLongSession)
    {
        $this->isLongSession = $isLongSession;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsLongSession()
    {
        return $this->isLongSession;
    }

    /**
     * @return bool
     */
    public function isLongSession()
    {
        return $this->getIsLongSession();
    }

    /**
     * @param string $token
     * @return $this
     */
    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return $this
     */
    public function createToken()
    {
        return $this->setToken($this->generateToken());
    }

    /**
     * @return string
     */
    public function generateToken()
    {
        return Token::generate(128);
    }

    /**
     * @param UserEntity $user
     * @return $this
     */
    public function setUser(UserEntity $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return UserEntity
     */
    public function getUser()
    {
        return $this->user;
    }
}