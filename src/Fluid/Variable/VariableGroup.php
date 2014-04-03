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
     * @param array $variables
     * @return $this
     */
    public function reset(array $variables = null)
    {
        $this->variables = null;
        if (is_array($variables)) {
            $this->variables = [];
            foreach ($variables as $data) {
                if (isset($data['name']) && isset($data['type'])) {
                    $variable = new VariableEntity();
                    $variable->setName($data['name']);
                    $variable->setType($data['type']);
                    if (isset($data['value'])) {
                        $variable->setValue($data['value']);
                    }
                    $this->add($variable);
                }
            }
        }
        return $this;
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