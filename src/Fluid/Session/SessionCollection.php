<?php
namespace Fluid\Session;

use Countable;
use Fluid\User\UserEntity;
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
     * @param StorageInterface $storage
     * @param UserEntity $user
     * @param SessionMapper|null $sessionMapper
     */
    public function __construct(StorageInterface $storage, UserEntity $user = null, SessionMapper $sessionMapper = null)
    {
        $this->setStorage($storage);
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
     * @return $this
     */
    public function delete(SessionEntity $session)
    {
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
        return $this->sessionMapper;
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