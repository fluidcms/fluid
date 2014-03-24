<?php
namespace Fluid\Session;

use Countable;
use Fluid\User\UserEntity;
use Fluid\User\UserCollection;
use IteratorAggregate;
use ArrayAccess;
use ArrayIterator;
use Fluid\StorageInterface;

class SessionCollection implements Countable, IteratorAggregate, ArrayAccess
{
    /**
     * @var SessionEntity[]
     */
    private $sessions;

    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var SessionMapper
     */
    private $sessionMapper;

    /**
     * @var UserCollection
     */
    private $userCollection;

    /**
     * @var UserEntity
     */
    private $user;

    /**
     * @param StorageInterface $storage
     * @param UserEntity $user
     * @param UserCollection $userCollection
     * @param SessionMapper|null $sessionMapper
     */
    public function __construct(StorageInterface $storage, UserCollection $userCollection, UserEntity $user = null, SessionMapper $sessionMapper = null)
    {
        $this->setStorage($storage);
        $this->setUserCollection($userCollection);
        if (null !== $user) {
            $this->setUser($user);
        }
        if (null !== $sessionMapper) {
            $this->setSessionMapper($sessionMapper);
        }
    }

    /**
     * @param SessionEntity $session
     * @return SessionEntity
     */
    public function add(SessionEntity $session)
    {
        $this->sessions[] = $session;
        return $session;
    }

    /**
     * @param SessionEntity $session
     * @return SessionEntity
     */
    public function create(SessionEntity $session)
    {
        if (null === $this->sessions) {
            $this->getSessionMapper()->mapCollection($this);
        }
        $this->sessions[] = $session;
        return $session;
    }

    /**
     * @param SessionEntity $session
     * @return $this
     */
    public function delete(SessionEntity $session)
    {
    }

    /**
     * @param SessionEntity $session
     */
    public function save(SessionEntity $session = null)
    {
        if (null === $this->sessions) {
            $this->getSessionMapper()->mapCollection($this);
        }

        if (null === $session) {
            $data = [];
            foreach ($this->sessions as $session) {
                $data[] = [
                    'token' => $session->getToken(),
                    'user_id' => $session->getUser()->getId(),
                    'is_long_session' => $session->getIsLongSession(),
                    'expiration_date' => $session->getExpirationDate()->format('Y-m-d H:i:s'),
                ];
            }
        } else {
            $data = $this->getSessionMapper()->getCollectionData($this);
            if (is_array($data)) {
                foreach ($data as $sessionKey => $sessionData) {
                    if ($sessionData['token'] === $session->getToken()) {
                        $data[$sessionKey] = [
                            'token' => $session->getToken(),
                            'user_id' => $session->getUser()->getId(),
                            'is_long_session' => $session->getIsLongSession(),
                            'expiration_date' => $session->getExpirationDate()->format('Y-m-d H:i:s'),
                        ];
                    }
                }
            } else {
                $data = [[
                    'token' => $session->getToken(),
                    'user_id' => $session->getUser()->getId(),
                    'is_long_session' => $session->getIsLongSession(),
                    'expiration_date' => $session->getExpirationDate()->format('Y-m-d H:i:s'),
                ]];
            }
        }
        $this->getSessionMapper()->persist($this, $data);
    }

    /**
     * @param StorageInterface $storage
     * @return $this
     */
    public function setStorage(StorageInterface $storage)
    {
        $this->storage = $storage;
        return $this;
    }

    /**
     * @return StorageInterface
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @param SessionMapper $sessionMapper
     * @return $this
     */
    public function setSessionMapper(SessionMapper $sessionMapper)
    {
        $this->sessionMapper = $sessionMapper;
        return $this;
    }

    /**
     * @return SessionMapper
     */
    public function getSessionMapper()
    {
        if (null === $this->sessionMapper) {
            $this->createSessionMapper();
        }
        return $this->sessionMapper;
    }

    /**
     * @return $this
     */
    public function createSessionMapper()
    {
        return $this->setSessionMapper(new SessionMapper($this->getStorage(), $this->getUserCollection()));
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

    /**
     * @return int
     */
    public function count()
    {
        if (null === $this->sessions) {
            $this->getSessionMapper()->mapCollection($this);
        }
        return count($this->sessions);
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        if (null === $this->sessions) {
            $this->getSessionMapper()->mapCollection($this);
        }
        return new ArrayIterator($this->sessions);
    }

    /**
     * @param int $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        if (null === $this->sessions) {
            $this->getSessionMapper()->mapCollection($this);
        }
        return isset($this->sessions[$offset]);
    }

    /**
     * @param int $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        if (null === $this->sessions) {
            $this->getSessionMapper()->mapCollection($this);
        }
        return $this->sessions[$offset];
    }

    /**
     * @param int $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        if (null === $this->sessions) {
            $this->getSessionMapper()->mapCollection($this);
        }
        $this->sessions[$offset] = $value;
    }

    /**
     * @param int $offset
     */
    public function offsetUnset($offset)
    {
        if (null === $this->sessions) {
            $this->getSessionMapper()->mapCollection($this);
        }
        unset($this->sessions[$offset]);
    }
}