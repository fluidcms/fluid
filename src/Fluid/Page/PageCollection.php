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
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @var PageEntity
     */
    private $parent;

    /**
     * @param RegistryInterface $registry
     * @param PageEntity|null $parent
     */
    public function __construct(RegistryInterface $registry, PageEntity $parent = null)
    {
        $this->setRegistry($registry);
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

        $this->registry->getPageMapper()->mapXmlObject($page, $mapping);

        return $page;
    }

    /**
     * @return PageEntity
     * @throws InvalidDataException
     */
    public function addGlobalPage()
    {
        return $this->addPage([
            'name' => PageEntity::GLOBAL_PAGE,
            'languages' => $this->getRegistry()->getConfig()->getLanguages(),
            'template' => PageEntity::GLOBAL_PAGE
        ], false);
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
                $page = new PageEntity($this->getRegistry());
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
        $pages = $this->pages;
        unset($pages[PageEntity::GLOBAL_PAGE]);
        return count($pages);
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        $pages = $this->pages;
        unset($pages[PageEntity::GLOBAL_PAGE]);
        return new ArrayIterator($pages);
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