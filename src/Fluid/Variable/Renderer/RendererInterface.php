<?php
namespace Fluid\Variable\Renderer;

use Fluid\Variable\VariableEntity;

interface RendererInterface
{
    /**
     * @return string
     */
    public function render();

    /**
     * @return VariableEntity
     */
    public function getVariable();

    /**
     * @param VariableEntity $variable
     * @return $this
     */
    public function setVariable(VariableEntity $variable);
}