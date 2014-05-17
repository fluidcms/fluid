<?php
namespace Fluid\Template;

use Fluid\Variable\VariableCollection;
use Fluid\XmlMappingLoaderInterface;

class TemplateEntity
{
    /**
     * @var string
     */
    private $template;

    /**
     * @var TemplateConfig
     */
    private $config;

    /**
     * @var VariableCollection
     */
    private $variables;

    /**
     * @var TemplateMapper
     */
    private $mapper;

    /**
     * @var bool
     */
    private $isMapped = false;

    /**
     * @var XmlMappingLoaderInterface
     */
    private $xmlMappingLoader;

    /**
     * @param string $template
     * @param VariableCollection $variables
     * @param XmlMappingLoaderInterface $xmlMappingLoader
     */
    public function __construct($template, VariableCollection $variables, XmlMappingLoaderInterface $xmlMappingLoader)
    {
        $this->setTemplate($template);
        $this->setVariables($variables);
        $this->setXmlMappingLoader($xmlMappingLoader);
        $this->setConfig(new TemplateConfig);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [];
    }

    /**
     * @param string $template
     * @return $this
     */
    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param VariableCollection $variables
     * @return $this
     */
    public function setVariables(VariableCollection $variables)
    {
        $this->variables = $variables;
        return $this;
    }

    /**
     * @return VariableCollection
     */
    public function getVariables()
    {
        if (!$this->isMapped()) {
            $this->getMapper()->map($this, $this->variables);
            $this->setIsMapped(true);
        }
        return $this->variables;
    }

    /**
     * @param bool $isMapped
     * @return $this
     */
    public function setIsMapped($isMapped)
    {
        $this->isMapped = $isMapped;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsMapped()
    {
        return $this->isMapped;
    }

    /**
     * @return bool
     */
    public function isMapped()
    {
        return $this->getIsMapped();
    }

    /**
     * @param TemplateMapper $mapper
     * @return $this
     */
    public function setMapper(TemplateMapper $mapper)
    {
        $this->mapper = $mapper;
        return $this;
    }

    /**
     * @return TemplateMapper
     */
    public function getMapper()
    {
        if (null === $this->mapper) {
            $this->createMapper();
        }
        return $this->mapper;
    }

    /**
     * @return $this
     */
    private function createMapper()
    {
        return $this->setMapper(new TemplateMapper($this->getXmlMappingLoader()));
    }

    /**
     * @param XmlMappingLoaderInterface $xmlMappingLoader
     * @return $this
     */
    public function setXmlMappingLoader(XmlMappingLoaderInterface $xmlMappingLoader)
    {
        $this->xmlMappingLoader = $xmlMappingLoader;
        return $this;
    }

    /**
     * @return XmlMappingLoaderInterface
     */
    public function getXmlMappingLoader()
    {
        return $this->xmlMappingLoader;
    }

    /**
     * @param TemplateConfig $config
     * @return $this
     */
    public function setConfig(TemplateConfig $config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @return TemplateConfig
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->getConfig()->getFile();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getConfig()->getName();
    }
}