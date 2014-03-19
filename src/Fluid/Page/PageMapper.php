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
     * @return PageEntity
     */
    public function mapXmlObject(PageEntity $page, array $mapping)
    {
        foreach ($mapping as $element) {
            if (isset($element['name']) && $element['name'] === 'setting') {
                if (isset($element['attributes']['name']) && isset($element['attributes']['value'])) {
                    $page->getConfig()->set($element['attributes']['name'], isset($element['attributes']['value']) ? $element['attributes']['value'] : null);
                }
            } elseif (isset($element['name']) && $element['name'] === 'page') {
                $page->getPages()->addPageMapping($element);
            }
        }

        return $page;
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