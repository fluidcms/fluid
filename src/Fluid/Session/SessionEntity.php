<?php
namespace Fluid\Session;

use DateTime;
use DateInterval;
use Fluid\Token;
use Fluid\User\UserCollection;
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

    /**
     * @var UserCollection
     */
    private $userCollection;

    /**
     * @param UserCollection $userCollection
     * @param UserEntity|null $user
     */
    public function __construct(UserCollection $userCollection, UserEntity $user = null)
    {
        $this->setUserCollection($userCollection);
        $this->createToken();
        $this->createExpirationDate();
        if (null !== $user) {
            $this->setUser($user);
        }
    }

    /**
     * @param array|string $attributes
     * @param mixed|null $value
     * @return $this
     */
    public function set($attributes, $value = null)
    {
        if (is_string($attributes)) {
            $attributes = [$attributes => $value];
        }

        foreach ($attributes as $key => $value) {
            if ($key === 'user_id') {
                $this->setUser($this->getUserCollection()->find($value));
            } elseif ($key === 'name') {
                $this->setName($value);
            } elseif ($key === 'password') {
                $this->setPassword($value);
            }
        }

        return $this;
    }

    /**
     * @param $attribute
     * @return mixed|null
     */
    public function get($attribute)
    {
        if ($attribute === 'id') {
            return $this->getId();
        } elseif ($attribute === 'email') {
            return $this->getEmail();
        } elseif ($attribute === 'name') {
            return $this->getName();
        } elseif ($attribute === 'password') {
            return $this->getPassword();
        }
        return null;
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

    /**
     * @param UserCollection $userCollection
     * @return $this
     */
    public function setUserCollection(UserCollection $userCollection)
    {
        $this->userCollection = $userCollection;
        return $this;
    }

    /**
     * @return UserCollection
     */
    public function getUserCollection()
    {
        return $this->userCollection;
    }
}