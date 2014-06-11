<?php
namespace Fluid\Variable;

use Fluid\Language\LanguageEntity;
use Fluid\RegistryInterface;
use Fluid\Variable\Renderer\RenderContent;
use JsonSerializable;

class VariableEntity implements JsonSerializable, VariableInterface
{
    const TYPE_STRING = 'string';
    const TYPE_CONTENT = 'content';
    const TYPE_IMAGE = 'image';

    /**
     * @var array
     */
    public static $types = [self::TYPE_STRING, self::TYPE_CONTENT];

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * @var mixed
     */
    private $value;

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
     * @param LanguageEntity $language
     */
    public function __construct(RegistryInterface $registry, LanguageEntity $language = null)
    {
        $this->registry = $registry;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        switch ($this->getType()) {
            case self::TYPE_STRING:
                return [
                    'name' => $this->getName(),
                    'type' => $this->getType(),
                    'value' => $this->getValue()
                ];
            case self::TYPE_IMAGE:
                return [
                    'name' => $this->getName(),
                    'type' => $this->getType(),
                    'attributes' => $this->getAttributes(),
                    'formats' => $this->getFormats()
                ];
            case self::TYPE_CONTENT:
                return [
                ];
        }
        return null;
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
     * @return string
     */
    public function renderValue()
    {
        switch ($this->getType()) {
            case self::TYPE_CONTENT:
                return (new RenderContent($this->registry, $this))->render();
            case self::TYPE_STRING:
                if ($this->getValue() === null) {
                    return '';
                }
                return $this->getValue();
            case self::TYPE_IMAGE:
                return '';
        }
        return null;
    }

    /**
     * @param $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param array|string $attributes
     * @param mixed|null $value
     */
    public function set($attributes, $value = null)
    {
        if (!is_array($attributes)) {
            $attributes = [$attributes => $value];
        }

        foreach ($attributes as $key => $value) {
            if ($key === 'name') {
                $this->setName($value);
            } elseif ($key === 'type') {
                $this->setType($value);
            }
        }
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
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function getFormats()
    {
        return array_values($this->formats);
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
        switch ($this->getType()) {
            case self::TYPE_IMAGE:
                if (isset($this->formats[$name])) {
                    return true;
                }
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
        switch ($this->getType()) {
            case self::TYPE_IMAGE:
                if (isset($this->formats[$name])) {
                    return $this->formats[$name]['attributes'];
                } else {
                    return $this->getAttributes();
                }
        }
        return $this->renderValue();
    }

    /**
     * @return mixed
     */
    public function __toString()
    {
        return $this->renderValue();
    }
}