<?php
namespace Fluid\Variable;

use JsonSerializable;
use Fluid\RegistryInterface;

class VariableImage implements JsonSerializable, VariableInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $formats;

    /**
     * @var array
     */
    private $attributes;

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
    }

    public function jsonSerialize()
    {
        return [
            'name' => $this->getName(),
            'type' => 'image',
            'attributes' => $this->getAttributes(),
            'formats' => $this->getFormats()
        ];
    }

    public function renderValue()
    {
        return '';
    }

    /**
     * @return array
     */
    public function getFormats()
    {
        return array_values($this->formats);
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
     * @param array $formats
     * @return $this
     */
    public function setFormats($formats)
    {
        $array = [];
        foreach ($formats as $value) {
            $array[$value['name']] = $value;
        }
        $this->formats = $array;
        return $this;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param array $attributes
     * @return $this
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
        return $this;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        if (isset($this->attributes[$name]) || isset($this->formats[$name])) {
            return true;
        }
        return false;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, array $arguments)
    {
        return $this->__get($name);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (isset($this->formats[$name])) {
            return $this->formats[$name]['attributes'];
        } else {
            return $this->getAttributes();
        }
    }

    /**
     * @return mixed
     */
    public function __toString()
    {
        return $this->renderValue();
    }
}