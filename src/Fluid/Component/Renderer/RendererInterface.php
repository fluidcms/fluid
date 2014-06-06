<?php
namespace Fluid\Component\Renderer;

use Fluid\Component\ComponentEntity;

interface RendererInterface
{
    /**
     * @return string
     */
    public function render();

    /**
     * @return ComponentEntity
     */
    public function getComponent();

    /**
     * @param ComponentEntity $component
     * @return $this
     */
    public function setComponent(ComponentEntity $component);
}