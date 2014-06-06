<?php
namespace Fluid\Component;

use Fluid\Component\Renderer\RenderComponent;
use Fluid\Data\DataCollection;
use JsonSerializable;
use Countable;
use Fluid\RegistryInterface;
use IteratorAggregate;
use ArrayAccess;
use ArrayIterator;
use Fluid\Variable\VariableCollection;
use Fluid\Component\Renderer\RendererInterface;

class ComponentEntity implements Countable, IteratorAggregate, ArrayAccess, JsonSerializable
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var ComponentConfig
     */
    private $config;

    /**
     * @var VariableCollection
     */
    private $variables;

    /**
     * @var string
     */
    private $xmlMappingFile;

    /**
     * @var ComponentCollection
     */
    private $collection;

    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @var RendererInterface
     */
    private $renderer;

    /**
     * @param RegistryInterface $registry
     * @param ComponentCollection|null $collection
     */
    public function __construct(RegistryInterface $registry, ComponentCollection $collection = null)
    {
        $this->registry = $registry;
        if (null !== $collection) {
            $this->setCollection($collection);
        }
        $this->setConfig(new ComponentConfig($this));
        $this->setVariables(new VariableCollection($this->registry));
    }

    /**
     * @return array
     * @deprecated
     */
    public function toArray()
    {
        return $this->jsonSerialize();
    }

    /**
     * @return array
     */
    public function jsonSerialize() {
        return [
            'config' => $this->getConfig()->toArray(),
            'variables' => $this->getVariables()->toArray()
        ];
    }

    /**
     * @return DataCollection
     */
    public function getData()
    {
        $data = $this->registry->getDataMapper();
        $data->setMap($this->registry->getMap());
        $data->setRequest($this->registry->getRouter()->getRequest());
        return $data->mapComponent($this);
    }

    /**
     * @return string
     */
    public function render()
    {
        if (null === $this->renderer) {
            $this->renderer = new RenderComponent($this->registry, $this);
        }
        return $this->renderer->render();
    }

    /**
     * @param ComponentCollection $collection
     * @return $this
     */
    public function setCollection(ComponentCollection $collection)
    {
        $this->collection = $collection;
        return $this;
    }

    /**
     * @return ComponentCollection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * @param ComponentConfig $config
     * @return $this
     */
    public function setConfig(ComponentConfig $config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @return ComponentConfig
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param VariableCollection $variables
     * @return $this
     */
    public function setVariables(VariableCollection $variables)
    {
        $this->variables = $variables;
        return $this;
    }

    /**
     * @return VariableCollection
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * @param string $xmlMappingFile
     * @return $this
     */
    public function setXmlMappingFile($xmlMappingFile)
    {
        $this->xmlMappingFile = $xmlMappingFile;
        return $this;
    }

    /**
     * @return string
     */
    public function getXmlMappingFile()
    {
        return $this->xmlMappingFile;
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
        $array = ['name', 'file', 'macro', 'icon'];
        return $this->getVariables()->count() + count($array);
    }


    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        $array = array_merge(
            [
                'name' => $this->getName(),
                'file' => $this->getConfig()->getFile(),
                'macro' => $this->getConfig()->getMacro(),
                'icon' => $this->getConfig()->getIcon()
            ],
            $this->getVariables()->getIterator()
        );
        return new ArrayIterator($array);
    }

    /**
     * @param int $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        if ($offset === 'name' || $offset === 'file' || $offset === 'macro' || $offset === 'icon') {
            return true;
        }
        return isset($this->getVariables()[$offset]);
    }

    /**
     * @param int $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        if ($offset === 'name') {
            return $this->getName();
        } elseif ($offset === 'file') {
            return $this->getConfig()->getFile();
        } elseif ($offset === 'macro') {
            return $this->getConfig()->getMacro();
        } elseif ($offset === 'icon') {
            return $this->getConfig()->getIcon();
        } else {
            return $this->getVariables()[$offset];
        }
    }

    /**
     * @param int $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        if ($offset === 'name') {
            $this->setName($value);
        } elseif ($offset === 'file') {
            $this->getConfig()->setFile($value);
        } elseif ($offset === 'macro') {
            $this->getConfig()->setMacro($value);
        } elseif ($offset === 'icon') {
            $this->getConfig()->setIcon($value);
        } else {
            $this->getVariables()[$offset] = $value;
        }
    }

    /**
     * @param int $offset
     */
    public function offsetUnset($offset)
    {
        if ($offset === 'name') {
            $this->setName(null);
        } elseif ($offset === 'file') {
            $this->getConfig()->setFile(null);
        } elseif ($offset === 'macro') {
            $this->getConfig()->setMacro(null);
        } elseif ($offset === 'icon') {
            $this->getConfig()->setIcon(null);
        } else {
            unset($this->getVariables()[$offset]);
        }
    }
}