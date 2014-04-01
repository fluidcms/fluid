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

    /**
     * @return array
     */
    public function toArray()
    {
        $variables = [];
        foreach ($this->variables as $variable) {
            $variables[] = $variable->toArray();
        }
        return [
            'name' => $this->getName(),
            'variables' => $variables
        ];
    }

    /**
     * @param VariableEntity $variable
     */
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