<?php
namespace Fluid;

use SimpleXMLElement;

class XmlMappingLoader implements XmlMappingLoaderInterface
{
    /**
     * @var Fluid
     */
    private $fluid;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @param Fluid $fluid
     */
    public function __construct(Fluid $fluid)
    {
        $this->setFluid($fluid);
        $this->setConfig($fluid->getConfig());
    }

    /**
     * @param string $filename
     * @return MappingInterface
     * @throws Exception\InvalidMappingPathException
     */
    public function load($filename)
    {
        $file = $this->getConfig()->getMapping() . DIRECTORY_SEPARATOR . $filename;
        if (file_exists($file)) {
            return new Mapping(new SimpleXMLElement($file, null, true));
        }
        return new Mapping();
    }

    /**
     * @param Fluid $fluid
     * @return $this
     */
    public function setFluid(Fluid $fluid)
    {
        $this->fluid = $fluid;
        return $this;
    }

    /**
     * @return Fluid
     */
    public function getFluid()
    {
        return $this->fluid;
    }

    /**
     * @param ConfigInterface $config
     * @return $this
     */
    public function setConfig(ConfigInterface $config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @return ConfigInterface
     */
    public function getConfig()
    {
        return $this->config;
    }
}