<?php
namespace Fluid\Variable;

use Fluid\Language\LanguageEntity;
use Fluid\RegistryInterface;
use Fluid\Variable\Renderer\RenderContent;
use JsonSerializable;

class VariableEntity implements JsonSerializable
{
    const TYPE_STRING = 'string';
    const TYPE_CONTENT = 'content';

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
        return [
            'name' => $this->getName(),
            'type' => $this->getType(),
            'value' => $this->getValue()
        ];
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
     * @return mixed
     */
    public function __toString()
    {
        return $this->renderValue();
    }
}