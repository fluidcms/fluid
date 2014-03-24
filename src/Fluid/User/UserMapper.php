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
     * @var UserCollection[]
     */
    private $collections;

    /**
     * @var array
     */
    private $collectionsData;

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
        $this->collections[spl_object_hash($userCollection)] = $userCollection;
        $data = $this->collectionsData[spl_object_hash($userCollection)] = $this->getStorage()->loadData(self::DATA_FILENAME);

        if (isset($data) && is_array($data)) {
            foreach ($data as $id => $userData) {
                $userCollection->add((new UserEntity($this->getStorage(), $userCollection))->setId($id)->set($userData));
            }
        }

        return $userCollection;
    }

    /**
     * @param UserCollection $userCollection
     * @param array $data
     * @return bool
     */
    public function persist(UserCollection $userCollection, array $data)
    {
        $this->collectionsData[spl_object_hash($userCollection)] = $data;
        return $this->getStorage()->saveData(self::DATA_FILENAME, $data);
    }

    /**
     * @param UserCollection $userCollection
     * @return array|null
     */
    public function getCollectionData(UserCollection $userCollection)
    {
        if (isset($this->collectionsData[spl_object_hash($userCollection)])) {
            return $this->collectionsData[spl_object_hash($userCollection)];
        }
        return null;
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