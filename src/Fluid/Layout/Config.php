<?php
namespace Fluid\Layout;

class Config
{
    /** @var string $file */
    private $file;

    /** @var string $name */
    private $name;

    /**
     * @param array $configs
     */
    public function __construct(array $configs)
    {
        foreach ($configs as $key => $value) {
            switch ($key) {
                case 'file':
                    $this->setFile($value);
                    break;
                case 'name':
                    $this->setName($value);
                    break;
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