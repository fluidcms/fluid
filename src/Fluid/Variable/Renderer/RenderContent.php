<?php
namespace Fluid\Variable\Renderer;

use Fluid\Variable\VariableEntity;

class RenderContent implements RendererInterface
{
    /**
     * @var VariableEntity
     */
    private $variable;

    /**
     * @param VariableEntity $variable
     */
    public function __construct(VariableEntity $variable)
    {
        $this->setVariable($variable);
    }

    /**
     * @return string
     */
    public function render()
    {
        $text = null;
        if (isset($this->variable->getValue()['text'])) {
            $text = $this->variable->getValue()['text'];
        }
        return $text;
    }

    /**
     * @return VariableEntity
     */
    public function getVariable()
    {
        return $this->variable;
    }

    /**
     * @param VariableEntity $variable
     * @return $this
     */
    public function setVariable(VariableEntity $variable)
    {
        $this->variable = $variable;
        return $this;
    }
}