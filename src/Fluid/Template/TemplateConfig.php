<?php
namespace Fluid\Template;

class TemplateConfig
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $file;

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
            } elseif ($key === 'file') {
                $this->setFile($value);
            }
        }
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