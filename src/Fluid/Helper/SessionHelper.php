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
     * @return bool
     */
    public function validSession()
    {
        if ($this->getSession() instanceof SessionEntity && !$this->getSession()->isExpired()) {
            return true;
        }
        return false;
    }

    /**
     * @param UserCollection $users
     * @param UserEntity $user
     * @param SessionCollection $sessions
     * @param SessionEntity $session
     * @return $this
     */
    public function setSessionDependencies(UserCollection $users, UserEntity $user, SessionCollection $sessions, SessionEntity $session)
    {
        $this->setUsers($users);
        $this->setUser($user);
        $this->setSessions($sessions);
        $this->setSession($session);
        return $this;
    }

    /**
     * @return SessionEntity
     */
    public function getSession()
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
    public function setSession(SessionEntity $session)
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
    public function setSessions(SessionCollection $sessions)
    {
        $this->sessions = $sessions;
        return $this;
    }

    /**
     * @return SessionCollection
     */
    public function getSessions()
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
        if (null === $this->session) {
            $this->createSession();
        }
        return $this->user;
    }

    /**
     * @param UserCollection $users
     * @return $this
     */
    public function setUsers(UserCollection $users)
    {
        $this->users = $users;
        return $this;
    }

    /**
     * @return UserCollection
     */
    public function getUsers()
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