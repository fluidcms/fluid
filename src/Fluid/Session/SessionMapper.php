<?php
namespace Fluid\Session;

use Fluid\StorageInterface;

class SessionMapper
{
    const DATA_FILENAME = 'sessions.json';

    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var SessionCollection[]
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
     * @param SessionCollection $sessionCollection
     * @return SessionCollection
     */
    public function mapCollection(SessionCollection $sessionCollection)
    {
        $this->collections[spl_object_hash($sessionCollection)] = $sessionCollection;
        $data = $this->collectionsData[spl_object_hash($sessionCollection)] = $this->getStorage()->loadData(self::DATA_FILENAME);

        if (isset($data) && is_array($data)) {
            foreach ($data as $id => $userData) {
                $sessionCollection->add((new SessionEntity())->set($userData));
            }
        }

        return $sessionCollection;
    }

    /**
     * @param SessionCollection $sessionCollection
     * @param array $data
     * @return bool
     */
    public function persist(SessionCollection $sessionCollection, array $data)
    {
        $this->collectionsData[spl_object_hash($sessionCollection)] = $data;
        return $this->getStorage()->saveData(self::DATA_FILENAME, $data);
    }

    /**
     * @param SessionCollection $sessionCollection
     * @return array|null
     */
    public function getCollectionData(SessionCollection $sessionCollection)
    {
        if (isset($this->collectionsData[spl_object_hash($sessionCollection)])) {
            return $this->collectionsData[spl_object_hash($sessionCollection)];
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