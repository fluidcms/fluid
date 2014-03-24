<?php
namespace Fluid\Helper;

use Fluid\Session\SessionEntity;
use Fluid\Session\SessionCollection;
use Fluid\CookieInterface;
use Fluid\StorageInterface;
use Fluid\User\UserEntity;
use Fluid\User\UserCollection;

trait SessionHelper
{
    /**
     * @return StorageInterface
     */
    abstract protected function getStorage();

    /**
     * @return CookieInterface
     */
    abstract protected function getCookie();

    /**
     * @var SessionEntity
     */
    protected $session;

    /**
     * @var UserEntity
     */
    protected $user;

    /**
     * @var UserCollection
     */
    protected $users;

    /**
     * @var SessionCollection
     */
    protected $sessions;

    /**
     * @return SessionEntity
     */
    protected function getSession()
    {
        if (null === $this->session) {
            $this->createSession();
        }
        return $this->session;
    }

    /**
     * @param SessionEntity $session
     * @return $this
     */
    protected function setSession(SessionEntity $session)
    {
        $this->session = $session;
        return $this;
    }

    /**
     * @return $this
     */
    private function createSession()
    {
        $sessions = $this->getSessions();
        $sessionToken = $this->getCookie()->get(SessionEntity::COOKIE_NAME);
        $session = $sessions->find($sessionToken);
        if ($session instanceof SessionEntity && !$session->isExpired()) {
            $this->setSession($session);
            $this->setUser($session->getUser());
        }
        return $this;
    }

    /**
     * @param SessionCollection $sessions
     * @return $this
     */
    protected function setSessions(SessionCollection $sessions)
    {
        $this->sessions = $sessions;
        return $this;
    }

    /**
     * @return SessionCollection
     */
    protected function getSessions()
    {
        if (null === $this->sessions) {
            $this->createSessions();
        }
        return $this->sessions;
    }

    /**
     * @return $this
     */
    private function createSessions()
    {
        return $this->setSessions(new SessionCollection($this->getStorage(), $this->getUsers()));
    }

    /**
     * @param UserEntity $user
     * @return $this
     */
    protected function setUser(UserEntity $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return UserEntity
     */
    protected function getUser()
    {
        return $this->user;
    }

    /**
     * @param UserCollection $users
     * @return $this
     */
    protected function setUsers(UserCollection $users)
    {
        $this->users = $users;
        return $this;
    }

    /**
     * @return UserCollection
     */
    protected function getUsers()
    {
        if (null === $this->users) {
            $this->createUsers();
        }
        return $this->users;
    }

    /**
     * @return $this
     */
    private function createUsers()
    {
        return $this->setUsers(new UserCollection($this->getStorage()));
    }
}