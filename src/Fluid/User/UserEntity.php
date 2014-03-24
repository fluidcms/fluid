<?php
namespace Fluid\User;

use Fluid\Session\SessionCollection;
use Fluid\StorageInterface;

class UserEntity
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var SessionCollection
     **/
    private $sessions;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string|null
     */
    private $name = null;

    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var UserCollection
     */
    private $userCollection;

    /**
     * @param StorageInterface $storage
     * @param UserCollection $userCollection
     */
    public function __construct(StorageInterface $storage, UserCollection $userCollection)
    {
        $this->setStorage($storage);
        $this->setUserCollection($userCollection);
        $this->setSessions(new SessionCollection($storage, $userCollection, $this));
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->getId(),
            'email' => $this->getEmail(),
            'name' => $this->getName()
        ];
    }

    /**
     * @param array|null $attributes
     * @return bool|array
     */
    public function validate(array $attributes = null)
    {
        $errors = [];
        if (null === $attributes) {
            $attributes = [
                'email' => $this->getEmail(),
                'name' => $this->getName(),
                'password' => $this->getPassword()
            ];
        }
        if (empty($attributes['email'])) {
            $errors['email'] = 'email';
        }
        if (empty($attributes['name'])) {
            $errors['name'] = 'name';
        }
        if (empty($attributes['password'])) {
            $errors['password'] = 'password';
        }
        if (count($errors)) {
            return $errors;
        }
        return true;
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
            if ($key === 'email') {
                $this->setEmail($value);
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
     * @param string $email
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param null|string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $password
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return string
     */
    public function hashPassword($password)
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * @param string $password
     * @return $this
     */
    public function createPasswordHash($password)
    {
        return $this->setPassword($this->hashPassword($password));
    }

    /**
     * @param string $password
     * @return bool
     */
    public function testPassword($password)
    {
        return password_verify($password, $this->getPassword());
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
        return $this->sessions;
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