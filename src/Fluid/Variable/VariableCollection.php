<?php
namespace Fluid\Variable;

use Countable;
use IteratorAggregate;
use ArrayAccess;
use ArrayIterator;
use Fluid\StorageInterface;
use Fluid\XmlMappingLoaderInterface;
use Fluid\Page\PageEntity;

class VariableCollection implements Countable, IteratorAggregate, ArrayAccess
{
    /**
     * @var array
     */
    protected $variables = [];

    /**
     * @var PageEntity
     */
    private $page;

    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var XmlMappingLoaderInterface
     */
    private $xmlMappingLoader;

    /**
     * @param PageEntity $page
     * @param StorageInterface $storage
     * @param XmlMappingLoaderInterface $xmlMappingLoader
     */
    public function __construct(PageEntity $page, StorageInterface $storage, XmlMappingLoaderInterface $xmlMappingLoader)
    {
        $this->setPage($page);
        $this->setStorage($storage);
        $this->setXmlMappingLoader($xmlMappingLoader);
    }

    /**
     * @param array $variables
     * @return $this
     */
    public function addVariables(array $variables)
    {
        return $this;
    }

    /**
     * @param PageEntity $page
     * @return $this
     */
    public function setPage(PageEntity $page)
    {
        $this->page = $page;
        return $this;
    }

    /**
     * @return PageEntity
     */
    public function getPage()
    {
        return $this->page;
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
     * @return int
     */
    public function count()
    {
        return count($this->variables);
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->variables);
    }

    /**
     * @param int $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->variables[$offset]);
    }

    /**
     * @param int $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->variables[$offset];
    }

    /**
     * @param int $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->variables[$offset] = $value;
    }

    /**
     * @param int $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->variables[$offset]);
    }
}