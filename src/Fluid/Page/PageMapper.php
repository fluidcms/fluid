<?php
namespace Fluid\Page;

use Fluid\RegistryInterface;
use Fluid\StorageInterface;
use Fluid\XmlMappingLoaderInterface;

class PageMapper
{
    /**
     * @var StorageInterface
     * @deprecated
     */
    private $storage;

    /**
     * @var XmlMappingLoaderInterface
     * @deprecated
     */
    private $xmlMappingLoader;

    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
        $this->setStorage($registry->getStorage());
        $this->setXmlMappingLoader($registry->getXmlMappingLoader());
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
     * @deprecated
     */
    public function setStorage(StorageInterface $storage)
    {
        $this->storage = $storage;
        return $this;
    }

    /**
     * @return StorageInterface
     * @deprecated
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @param XmlMappingLoaderInterface $xmlMappingLoader
     * @return $this
     * @deprecated
     */
    public function setXmlMappingLoader(XmlMappingLoaderInterface $xmlMappingLoader)
    {
        $this->xmlMappingLoader = $xmlMappingLoader;
        return $this;
    }

    /**
     * @return XmlMappingLoaderInterface
     * @deprecated
     */
    public function getXmlMappingLoader()
    {
        return $this->xmlMappingLoader;
    }
}