<?php
namespace Fluid\Variable\Renderer;

use Fluid\Component\ComponentEntity;
use Fluid\Component\ComponentMapper;
use Fluid\RegistryInterface;
use Fluid\Variable\VariableEntity;

class RenderContent implements RendererInterface
{
    /**
     * @var string
     */
    private $imageHtml = '<img src="%s" width="%s" height="%s" alt="%s">';

    /**
     * @var VariableEntity
     */
    private $variable;

    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @param RegistryInterface $registry;
     * @param VariableEntity $variable
     */
    public function __construct(RegistryInterface $registry, VariableEntity $variable)
    {
        $this->registry = $registry;
        $this->setVariable($variable);
    }

    /**
     * @param array $images
     * @param string $text
     * @return string
     */
    private function renderImages(array $images, $text)
    {
        foreach ($images as $image) {
            if (isset($image['id']) && isset($image['src'])) {
                $imageHtml = sprintf(
                    $this->imageHtml,
                    $image['src'],
                    isset($image['width']) ? $image['width'] : '',
                    isset($image['height']) ? $image['height'] : '',
                    isset($image['alt']) ? $image['alt'] : ''
                );
                $text = str_replace("{{$image['id']}}", $imageHtml, $text);
            }
        }
        return $text;
    }

    /**
     * @param array $components
     * @param string $text
     * @return string
     */
    private function renderComponents(array $components, $text)
    {
        $componentMapper = new ComponentMapper($this->registry);
        foreach ($components as $component) {
            $componentMapper->mapObject($component);
            var_dump($component); die();
            new RenderComponent();
        }
        return $text;
    }

    /**
     * @return string
     */
    public function render()
    {
        $text = null;
        $images = [];
        $components = [];
        $files = [];
        if (isset($this->variable->getValue()['text'])) {
            $text = $this->variable->getValue()['text'];
        }
        if (isset($this->variable->getValue()['images']) && is_array($this->variable->getValue()['images'])) {
            $images = $this->variable->getValue()['images'];
        }
        if (isset($this->variable->getValue()['components']) && is_array($this->variable->getValue()['components'])) {
            $components = $this->variable->getValue()['components'];
        }
        if (isset($this->variable->getValue()['files']) && is_array($this->variable->getValue()['files'])) {
            $files = $this->variable->getValue()['files'];
        }
        if (count($images)) {
            $text = $this->renderImages($images, $text);
        }
        if (count($components)) {
            $text = $this->renderComponents($components, $text);
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