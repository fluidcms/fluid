<?php
namespace Fluid\Page;

use Countable;
use IteratorAggregate;
use ArrayAccess;
use ArrayIterator;
use Fluid\Language\LanguageEntity;
use Fluid\StorageInterface;
use Fluid\XmlMappingLoaderInterface;
use Fluid\Exception\MissingMappingAttributeException;
use Fluid\Exception\InvalidDataException;
use Fluid\RegistryInterface;

/**
 * Class PageCollection
 * @package Fluid\Page
 */
class PageCollection implements Countable, IteratorAggregate, ArrayAccess
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var PageEntity[]
     */
    protected $pages = [];

    /**
     * @var StorageInterface
     * @deprecated Use registry instead
     */
    private $storage;

    /**
     * @var PageMapper
     */
    private $mapper;

    /**
     * @var XmlMappingLoaderInterface
     * @deprecated Use registry instead
     */
    private $xmlMappingLoader;

    /**
     * @var LanguageEntity
     */
    private $language;

    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @var PageEntity
     */
    private $parent;

    /**
     * @param RegistryInterface $registry
     * @param StorageInterface $storage
     * @param XmlMappingLoaderInterface $xmlMappingLoader
     * @param PageMapper|null $pageMapper
     * @param LanguageEntity $language
     * @param PageEntity|null $parent
     */
    public function __construct(RegistryInterface $registry, StorageInterface $storage, XmlMappingLoaderInterface $xmlMappingLoader, PageMapper $pageMapper = null, LanguageEntity $language, PageEntity $parent = null)
    {
        $this->setRegistry($registry);
        $this->setStorage($storage);
        $this->setXmlMappingLoader($xmlMappingLoader);
        if (null !== $pageMapper) {
            $this->setMapper($pageMapper);
        } else {
            $this->setMapper(new PageMapper($storage, $xmlMappingLoader));
        }
        $this->setLanguage($language);
        if (null !== $parent) {
            $this->setParent($parent);
        }
    }

    /**
     * @param string $path
     * @return PageEntity
     */
    public function find($path)
    {
        if (!strstr($path, '/')) {
            $path = [$path];
        } else {
            $path = explode('/', $path);
        }

        if (isset($path[0]) && isset($this->pages[$path[0]])) {
            $page = $this->pages[$path[0]];
            array_shift($path);
            if (count($path)) {
                if ($page instanceof PageEntity) {
                    return $page->getPages()->find(implode('/', $path));
                } else {
                    return null;
                }
            } else {
                return $page;
            }
        }
        return null;
    }

    /**
     * @param PageEntity $page
     * @param int $order
     */
    public function order(PageEntity $page, $order)
    {
        $retval = [];
        $count = 0;
        foreach ($this->pages as $key => $item) {
            if ($count === $order) {
                $retval[$page->getName()] = $page;
            }
            if ($page !== $item) {
                $retval[$item->getName()] = $item;
            }
            $count++;
        }
        $this->pages = $retval;
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
            $page = $this->addPage(['name' => $mapping['attributes']['name']], false);
        }

        $this->getMapper()->mapXmlObject($page, $mapping);

        return $page;
    }

    /**
     * @param array $data
     * @param bool $search
     * @return PageEntity
     * @throws InvalidDataException
     */
    public function addPage(array $data, $search = true)
    {
        if (isset($data['name'])) {
            if ($search) {
                $page = $this->find($data['name']);
            }
            if (!isset($page)) {
                $page = new PageEntity($this->getRegistry(), $this->getStorage(), $this->getXmlMappingLoader(), $this->getMapper(), $this->getLanguage());
            }

            $this->pages[$data['name']] = $page;
            $page->set($data);
            if (null !== $this->getParent()) {
                $page->setParent($this->getParent());
            }
            return $page;
        }
        throw new InvalidDataException();
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
     * @deprecated Use registry instead
     */
    public function setStorage(StorageInterface $storage)
    {
        $this->storage = $storage;
        return $this;
    }

    /**
     * @return StorageInterface
     * @deprecated Use registry instead
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @param XmlMappingLoaderInterface $xmlMappingLoader
     * @return $this
     * @deprecated Use registry instead
     */
    public function setXmlMappingLoader(XmlMappingLoaderInterface $xmlMappingLoader)
    {
        $this->xmlMappingLoader = $xmlMappingLoader;
        return $this;
    }

    /**
     * @return XmlMappingLoaderInterface
     * @deprecated Use registry instead
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

    /**
     * @param LanguageEntity $language
     * @return $this
     */
    public function setLanguage(LanguageEntity $language)
    {
        $this->language = $language;
        return $this;
    }

    /**
     * @return LanguageEntity
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @return PageEntity
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param PageEntity $parent
     * @return $this
     */
    public function setParent(PageEntity $parent)
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return $this->offsetExists($name);
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, array $arguments)
    {
        return $this->offsetGet($name);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->offsetGet($name);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->pages);
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->pages);
    }

    /**
     * @param int $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->pages[$offset]);
    }

    /**
     * @param int $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->pages[$offset];
    }

    /**
     * @param int $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->pages[$offset] = $value;
    }

    /**
     * @param int $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->pages[$offset]);
    }
}