<?php
namespace Fluid\Variable;

use Countable;
use Fluid\Language\LanguageEntity;
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
    protected $variables;

    /**
     * @var PageEntity
     */
    private $page;

    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var VariableMapper
     */
    private $mapper;

    /**
     * @var XmlMappingLoaderInterface
     */
    private $xmlMappingLoader;

    /**
     * @var LanguageEntity
     */
    private $language;

    /**
     * @param PageEntity $page
     * @param StorageInterface $storage
     * @param XmlMappingLoaderInterface $xmlMappingLoader
     * @param null|VariableMapper $mapper
     * @param LanguageEntity $language
     */
    public function __construct(PageEntity $page, StorageInterface $storage, XmlMappingLoaderInterface $xmlMappingLoader, VariableMapper $mapper = null, LanguageEntity $language)
    {
        $this->setLanguage($language);
        $this->setPage($page);
        $this->setStorage($storage);
        $this->setXmlMappingLoader($xmlMappingLoader);
        if (null !== $mapper) {
            $this->setMapper($mapper);
        }
    }

    /**
     * @param VariableEntity|VariableGroup $variable
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addVariable($variable)
    {
        if (!$variable instanceof VariableGroup && !$variable instanceof VariableEntity) {
            throw new \InvalidArgumentException;
        }
        $this->variables[] = $variable;
        return $this;
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
     * @param VariableMapper $mapper
     * @return $this
     */
    public function setMapper(VariableMapper $mapper)
    {
        $this->mapper = $mapper;
        return $this;
    }

    /**
     * @return VariableMapper
     */
    public function getMapper()
    {
        if (null === $this->mapper) {
            $this->createMapper();
        }
        return $this->mapper;
    }

    /**
     * @return $this
     */
    private function createMapper()
    {
        return $this->setMapper(new VariableMapper($this->getLanguage()));
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
     * @return int
     */
    public function count()
    {
        if (null === $this->variables) {
            $this->getMapper()->mapCollection($this);
        }
        return count($this->variables);
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        if (null === $this->variables) {
            $this->getMapper()->mapCollection($this);
        }
        return new ArrayIterator($this->variables);
    }

    /**
     * @param int $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        if (null === $this->variables) {
            $this->getMapper()->mapCollection($this);
        }
        return isset($this->variables[$offset]);
    }

    /**
     * @param int $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        if (null === $this->variables) {
            $this->getMapper()->mapCollection($this);
        }
        if (isset($this->variables[$offset])) {
            return $this->variables[$offset];
        }
        return null;
    }

    /**
     * @param int $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        if (null === $this->variables) {
            $this->getMapper()->mapCollection($this);
        }
        $this->variables[$offset] = $value;
    }

    /**
     * @param int $offset
     */
    public function offsetUnset($offset)
    {
        if (null === $this->variables) {
            $this->getMapper()->mapCollection($this);
        }
        unset($this->variables[$offset]);
    }
}