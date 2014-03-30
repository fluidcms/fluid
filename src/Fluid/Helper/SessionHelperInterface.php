<?php
namespace Fluid\Helper;

use Fluid\Session\SessionEntity;
use Fluid\Session\SessionCollection;
use Fluid\User\UserEntity;
use Fluid\User\UserCollection;

interface SessionHelperInterface
{
    /**
     * @param UserCollection $users
     * @param UserEntity $user
     * @param SessionCollection $sessions
     * @param SessionEntity $session
     * @return $this
     */
    function setSessionDepenencies(UserCollection $users, UserEntity $user, SessionCollection $sessions, SessionEntity $session);

    /**
     * @return SessionEntity
     */
    function getSession();

    /**
     * @param SessionEntity $session
     * @return $this
     */
    function setSession(SessionEntity $session);

    /**
     * @param SessionCollection $sessions
     * @return $this
     */
    function setSessions(SessionCollection $sessions);

    /**
     * @return SessionCollection
     */
    function getSessions();

    /**
     * @param UserEntity $user
     * @return $this
     */
    function setUser(UserEntity $user);

    /**
     * @return UserEntity
     */
    function getUser();

    /**
     * @param UserCollection $users
     * @return $this
     */
    function setUsers(UserCollection $users);

    /**
     * @return UserCollection
     */
    function getUsers();

    /**
     * @return bool
     */
    function validSession();
}