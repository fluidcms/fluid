<?php
namespace Fluid\Component\Renderer;

use Fluid\RegistryInterface;
use Fluid\Component\ComponentEntity;

class RenderComponent implements RendererInterface
{
    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @var ComponentEntity
     */
    private $component;

    /**
     * @param RegistryInterface $registry
     * @param ComponentEntity $component
     */
    public function __construct(RegistryInterface $registry, ComponentEntity $component)
    {
        $this->registry = $registry;
        $this->setComponent($component);
    }

    /**
     * @return string
     */
    public function render()
    {
        $render = $this->registry->getTemplateEngine()->renderCompontent($this->component);
        return $this->getRegistry()->getTemplateEngine()->renderPage($this->page);
    }

    /**
     * @return ComponentEntity
     */
    public function getComponent()
    {
        return $this->component;
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
}