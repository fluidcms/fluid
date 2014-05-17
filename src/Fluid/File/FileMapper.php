<?php
namespace Fluid\File;

use Fluid\RegistryInterface;
use Fluid\StorageInterface;
use Fluid\XmlMappingLoaderInterface;

class FileMapper
{
    const FILES_DIRECTORY = 'files';

    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var XmlMappingLoaderInterface
     */
    private $xmlMappingLoader;

    /**
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        $this->setRegistry($registry);
        $this->setStorage($registry->getStorage());
        $this->setXmlMappingLoader($registry->getXmlMappingLoader());
    }

    /**
     * @param FileCollection|FileEntity[] $collection
     * @return FileCollection
     */
    public function mapCollection(FileCollection $collection)
    {
        $files = $this->getStorage()->getBranchFileList(self::FILES_DIRECTORY);
        foreach ($files as $fileid) {
            if (is_dir($fileid)) {
                $fileid = basename($fileid);
                $file = new FileEntity($this->getRegistry());
                $file->setId($fileid);
                $file = $this->mapEntity($file);
                if ($file !== null) {
                    $collection->add($file);
                }
            }
        }
        return $collection;
    }

    /**
     * @param FileEntity $file
     * @return FileEntity
     */
    public function mapEntity(FileEntity $file)
    {
        $found = false;
        $id = $file->getId();
        $fileDir = $this->getStorage()->getBranchFileList(self::FILES_DIRECTORY . DIRECTORY_SEPARATOR . $id);
        foreach ($fileDir as $item) {
            if (is_file($item)) {
                $file->setName(basename($item));
                $found = true;
            }
        }

        if ($found) {
            return $file;
        }

        return null;
    }

    /**
     * @param FileEntity $file
     * @param array $uploadedFile
     * @return null|FileEntity
     */
    public function persist(FileEntity $file, array $uploadedFile)
    {
        if ($file->validate($uploadedFile)) {
            $tmpfile = $uploadedFile['tmp_name'];
            $newfile = self::FILES_DIRECTORY . DIRECTORY_SEPARATOR . $file->getId() . DIRECTORY_SEPARATOR . $file->getName();

            if (!$this->getStorage()->branchFileExists($newfile)) {
                $this->getStorage()->uploadBranchFile($tmpfile, $newfile);
                return $file;
            }
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

    /**
     * @param XmlMappingLoaderInterface $xmlMappingLoader
     * @return $this
     */
    public function setXmlMappingLoader(XmlMappingLoaderInterface $xmlMappingLoader)
    {
        $this->xmlMappingLoader = $xmlMappingLoader;
        return $this;
    }

    /**
     * @return XmlMappingLoaderInterface
     */
    public function getXmlMappingLoader()
    {
        return $this->xmlMappingLoader;
    }

    /**
     * @return RegistryInterface
     */
    public function getRegistry()
    {
        return $this->registry;
    }

    /**
     * @param RegistryInterface $registry
     * @return $this
     */
    public function setRegistry(RegistryInterface $registry)
    {
        $this->registry = $registry;
        return $this;
    }
}