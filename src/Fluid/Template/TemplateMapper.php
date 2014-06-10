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

        $this->registry->getVariableMapper()->mapXmlObject($mapping,  $variables);
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