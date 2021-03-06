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
     * @param UserEntity $user
     * @return UserEntity
     */
    public function create(UserEntity $user)
    {
        if (null === $this->users) {
            $this->getUserMapper()->mapCollection($this);
        }
        $id = 0;
        if (isset($this->users) && count($this->users)) {
            foreach ($this->users as $user) {
                if ($user->getId() >= $id) {
                    $id = $user->getId();
                }
            }
        }
        $id++;
        $user->setId($id);
        $this->users[] = $user;
        return $user;
    }

    /**
     * @param mixed $id
     * @return null|UserEntity
     */
    public function find($id)
    {
        return $this->findOneBy(['id' => $id]);
    }

    /**
     * @param array $params
     * @param bool $findOne
     * @return null|UserEntity[]|array
     */
    public function findBy(array $params, $findOne = false)
    {
        if (null === $this->users) {
            $this->getUserMapper()->mapCollection($this);
        }
        $retval = [];
        if (count($params) && is_array($this->users)) {
            foreach ($this->users as $user) {
                $match = true;
                foreach ($params as $key => $param) {
                    $haystack = $user->get($key);
                    if ($haystack != $param) {
                        $match = false;
                    }
                }
                if ($match === true && $findOne) {
                    return $user;
                } elseif ($match === true) {
                    $retval[] = $user;
                }
            }
        }
        if (count($retval)) {
            return $retval;
        }
        return null;
    }

    /**
     * @param array $params
     * @return null|UserEntity
     */
    public function findOneBy(array $params)
    {
        return $this->findBy($params, true);
    }

    /**
     * @param UserEntity $user
     */
    public function save(UserEntity $user = null)
    {
        if (null === $this->users) {
            $this->getUserMapper()->mapCollection($this);
        }

        if (null === $user) {
            $data = [];
            foreach ($this->users as $user) {
                $data[$user->getId()] = [
                    'email' => $user->getEmail(),
                    'name' => $user->getName(),
                    'password' => $user->getPassword()
                ];
            }
        } else {
            $data = $this->getUserMapper()->getCollectionData($this);
            $data[$user->getId()] = [
                'email' => $user->getEmail(),
                'name' => $user->getName(),
                'password' => $user->getPassword()
            ];
        }
        $this->getUserMapper()->persist($this, $data);
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
        if (null === $this->users) {
            $this->getUserMapper()->mapCollection($this);
        }
        return count($this->users);
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        if (null === $this->users) {
            $this->getUserMapper()->mapCollection($this);
        }
        return new ArrayIterator($this->users);
    }

    /**
     * @param int $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        if (null === $this->users) {
            $this->getUserMapper()->mapCollection($this);
        }
        return isset($this->users[$offset]);
    }

    /**
     * @param int $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        if (null === $this->users) {
            $this->getUserMapper()->mapCollection($this);
        }
        return $this->users[$offset];
    }

    /**
     * @param int $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        if (null === $this->users) {
            $this->getUserMapper()->mapCollection($this);
        }
        $this->users[$offset] = $value;
    }

    /**
     * @param int $offset
     */
    public function offsetUnset($offset)
    {
        if (null === $this->users) {
            $this->getUserMapper()->mapCollection($this);
        }
        unset($this->users[$offset]);
    }
}