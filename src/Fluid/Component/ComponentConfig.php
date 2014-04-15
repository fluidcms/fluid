<?php
namespace Fluid\Component;

class ComponentConfig
{
    /**
     * @var ComponentEntity
     */
    private $component;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $file;

    /**
     * @var string
     */
    private $macro;

    /**
     * @var string
     */
    private $icon;

    /**
     * @param ComponentEntity $component
     */
    public function __construct(ComponentEntity $component)
    {
        $this->setComponent($component);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'name' => $this->getName(),
            'file' => $this->getFile(),
            'macro' => $this->getMacro(),
            'icon' => $this->getIcon()
        ];
    }

    /**
     * @param array|string $attributes
     * @param mixed|null $value
     */
    public function set($attributes, $value = null)
    {
        if (is_string($attributes)) {
            $attributes = [$attributes => $value];
        }

        foreach ($attributes as $key => $value) {
            if ($key === 'name') {
                $this->setName($value);
            } elseif ($key === 'file') {
                $this->setFile($value);
            } elseif ($key === 'macro') {
                $this->setMacro($value);
            } elseif ($key === 'icon') {
                $this->setIcon($value);
            }
        }
    }

    /**
     * @param ComponentEntity $component
     * @return $this
     */
    public function setComponent(ComponentEntity $component)
    {
        $this->component = $component;
        return $this;
    }

    /**
     * @return ComponentEntity
     */
    public function getComponent()
    {
        return $this->component;
    }

    /**
     * @param string $file
     * @return $this
     */
    public function setFile($file)
    {
        $this->file = $file;
        return $this;
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param string $icon
     * @return $this
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @param string $macro
     * @return $this
     */
    public function setMacro($macro)
    {
        $this->macro = $macro;
        return $this;
    }

    /**
     * @return string
     */
    public function getMacro()
    {
        return $this->macro;
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
}