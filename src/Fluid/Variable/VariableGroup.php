<?php
namespace Fluid\Variable;

class VariableGroup
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array|VariableEntity[]
     */
    private $variables;

    public function add(VariableEntity $variable)
    {
        $this->variables[] = $variable;
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