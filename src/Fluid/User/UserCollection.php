<?php
namespace Fluid\User;

use Countable;
use IteratorAggregate;
use ArrayAccess;
use ArrayIterator;
use Fluid\StorageInterface;

class UserCollection implements Countable, IteratorAggregate, ArrayAccess
{
    /**
     * @var UserEntity[]
     */
    private $users;

    /**
     * @var UserMapper
     */
    private $userMapper;

    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @param StorageInterface $storage
     * @param UserMapper|null $userMapper
     */
    public function __construct(StorageInterface $storage, UserMapper $userMapper = null)
    {
        $this->setStorage($storage);
        if (null !== $userMapper) {
            $this->setUserMapper($userMapper);
        }
    }

    /**
     * @param UserEntity $user
     * @return UserEntity
     */
    public function add(UserEntity $user)
    {
        $this->users[] = $user;
        return $user;
    }

    /**
     * @param array $params
     * @return null|UserEntity
     */
    public function findBy(array $params)
    {
        if (null === $this->users) {
            $this->getUserMapper()->mapCollection($this);
        }
        return null;
    }

    /**
     * @param UserMapper $userMapper
     * @return $this
     */
    public function setUserMapper(UserMapper $userMapper)
    {
        $this->userMapper = $userMapper;
        return $this;
    }

    /**
     * @return UserMapper
     */
    public function getUserMapper()
    {
        if (null === $this->userMapper) {
            $this->createUserMapper();
        }
        return $this->userMapper;
    }

    /**
     * @return $this
     */
    public function createUserMapper()
    {
        return $this->setUserMapper(new UserMapper($this->getStorage()));
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
     * @return int
     */
    public function count()
    {
        return count($this->users);
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->users);
    }

    /**
     * @param int $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->users[$offset]);
    }

    /**
     * @param int $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->users[$offset];
    }

    /**
     * @param int $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->users[$offset] = $value;
    }

    /**
     * @param int $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->users[$offset]);
    }
}