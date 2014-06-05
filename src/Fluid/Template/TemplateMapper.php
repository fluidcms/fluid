<?php
namespace Fluid\Template;

use Fluid\RegistryInterface;
use Fluid\Variable\VariableCollection;
use Fluid\Variable\VariableEntity;
use Fluid\Variable\VariableGroup;
use Fluid\XmlMappingLoaderInterface;

class TemplateMapper
{
    const MAPPING_DIRECTORY = 'templates';

    /**
     * @var XmlMappingLoaderInterface
     * @deprecated
     */
    private $xmlMappingLoader;

    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
        $this->setXmlMappingLoader($registry->getXmlMappingLoader());
    }

    /**
     * @param TemplateEntity $template
     * @param VariableCollection $variables
     */
    public function map(TemplateEntity $template, VariableCollection $variables)
    {
        $mapping = $this->getXmlMappingLoader()->load(self::MAPPING_DIRECTORY . DIRECTORY_SEPARATOR . $template->getTemplate() . '.xml');
        $template->getConfig()->set($mapping->getConfig());

        foreach ($mapping->getContent() as $key => $value) {
            if (isset($value['name']) && $value['name'] === 'variable') {
                $attributes = isset($value['attributes']) ? $value['attributes'] : [];
                $variable = new VariableEntity($this->registry);
                $variable->set($attributes);
                $variables->addVariable($variable);
            } elseif (isset($value['name']) && $value['name'] === 'group') {
                $attributes = isset($value['attributes']) ? $value['attributes'] : [];
                $variableGroup = new VariableGroup($this->registry);
                $variableGroup->setName(isset($attributes['name']) ? $attributes['name'] : null);
                foreach ($value as $groupVariable) {
                    if (isset($groupVariable['name']) && $groupVariable['name'] === 'variable') {
                        $attributes = isset($groupVariable['attributes']) ? $groupVariable['attributes'] : [];
                        $variable = new VariableEntity($this->registry);
                        $variable->set($attributes);
                        $variableGroup->add($variable);
                    }
                }
                $variables->addVariable($variableGroup);
            }
        }
    }

    /**
     * @param XmlMappingLoaderInterface $xmlMappingLoader
     * @return $this
     * @deprecated
     */
    public function setXmlMappingLoader(XmlMappingLoaderInterface $xmlMappingLoader)
    {
        $this->xmlMappingLoader = $xmlMappingLoader;
        return $this;
    }

    /**
     * @return XmlMappingLoaderInterface
     * @deprecated
     */
    public function getXmlMappingLoader()
    {
        return $this->xmlMappingLoader;
    }
}