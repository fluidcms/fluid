<?php
namespace Fluid\Page;

use Fluid\StorageInterface;
use Fluid\XmlMappingLoaderInterface;
use Fluid\Exception\MissingMappingAttributeException;

/**
 * Class PageRepository
 * @package Fluid\Page
 */
class PageRepository
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var PageEntity[]
     */
    private $pages = [];

    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var PageMapper
     */
    private $mapper;

    /**
     * @var XmlMappingLoaderInterface
     */
    private $xmlMappingLoader;

    /**
     * @param StorageInterface $storage
     * @param XmlMappingLoaderInterface $xmlMappingLoader
     * @param PageMapper|null $pageMapper
     */
    public function __construct(StorageInterface $storage, XmlMappingLoaderInterface $xmlMappingLoader, PageMapper $pageMapper = null)
    {
        $this->setStorage($storage);
        $this->setXmlMappingLoader($xmlMappingLoader);
        if (null !== $pageMapper) {
            $this->setMapper($pageMapper);
        } else {
            $this->setMapper(new PageMapper($storage, $xmlMappingLoader));
        }
    }

    /**
     * @param $path
     * @return PageEntity
     */
    public function find($path)
    {
        $path = explode('/', $path);
        if (isset($path[0]) && isset($this->pages[$path[0]])) {
            array_shift($path);
            if (count($path)) {
                if ($this->pages[$path[0]] instanceof PageEntity) {
                    return $this->pages[$path[0]]->getPages()->find(implode('/', $path));
                } else {
                    return null;
                }
            } else {
                return $this->pages[$path[0]];
            }
        }
        return null;
    }

    /**
     * @param array $mapping
     * @return PageEntity
     * @throws MissingMappingAttributeException
     */
    public function addPageMapping(array $mapping)
    {
        $path = null;
        if ($this->getPath() !== null) {
            $path = $this->getPath() . '/';
        }
        if (!isset($mapping['attributes']['name'])) {
            throw new MissingMappingAttributeException('Pages in map mapping requires a name attribute');
        }
        $path .= $mapping['attributes']['name'];
        $page = $this->find($path);
        if (!isset($page)) {
            $page = $this->addPage(['name' => $mapping['attributes']['name']]);
        }

        $this->getMapper()->mapXmlObject($page, $mapping);

        return $page;
    }

    /**
     * @param array $page
     * @return PageEntity
     */
    public function addPage(array $page)
    {
        return new PageEntity();
    }

    /**
     * @param array $pages
     * @return $this
     */
    public function addPages(array $pages = null)
    {
        if (is_array($pages)) {
            foreach ($pages as $page) {
                $this->addPage($page);
            }
        }
        return $this;
    }

    /**
     * @param string $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
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
     * @param PageMapper $mapper
     * @return $this
     */
    public function setMapper(PageMapper $mapper)
    {
        $this->mapper = $mapper;
        return $this;
    }

    /**
     * @return PageMapper
     */
    public function getMapper()
    {
        return $this->mapper;
    }
}