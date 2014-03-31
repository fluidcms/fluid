<?php
namespace Fluid\Variable;

class VariableGroup
{
    /**
     * @var array|VariableEntity[]
     */
    private $variables;

    public function add(VariableEntity $variable)
    {
        $this->variables[] = $variable;
    }
}