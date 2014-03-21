<?php
namespace Fluid\User;

use Fluid\StorageInterface;

class UserMapper
{
    const DATA_FILENAME = 'users.json';

    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @param StorageInterface $storage
     */
    public function __construct(StorageInterface $storage)
    {
        $this->setStorage($storage);
    }

    /**
     * @param UserCollection $userCollection
     * @return UserCollection
     */
    public function mapCollection(UserCollection $userCollection)
    {
        $data = $this->getStorage()->loadData(self::DATA_FILENAME);

        if (isset($data) && is_array($data)) {
            foreach ($data as $id => $userData) {
                $userCollection->add((new UserEntity)->setId($id)->set($userData));
            }
        }

        return $userCollection;
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
}