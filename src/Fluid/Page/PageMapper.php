<?php
namespace Fluid\Page;

use Fluid\StorageInterface;
use Fluid\XmlMappingLoaderInterface;

class PageMapper
{
    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var XmlMappingLoaderInterface
     */
    private $xmlMappingLoader;

    /**
     * @param StorageInterface $storage
     * @param XmlMappingLoaderInterface $xmlMappingLoader
     */
    public function __construct(StorageInterface $storage, XmlMappingLoaderInterface $xmlMappingLoader)
    {
        $this->setStorage($storage);
        $this->setXmlMappingLoader($xmlMappingLoader);
    }

    /**
     * @param PageEntity $page
     * @param array $mapping
     */
    public function mapXmlObject(PageEntity $page, array $mapping)
    {

        /*$content = $mapping->getContent();
        if (is_array($content)) {
            foreach ($content as $item) {
                if (isset($item['name']) && $item['name'] === 'page') {
                    $map->getPages()->addPageMapping($item);
                }
            }
        }*/
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
}