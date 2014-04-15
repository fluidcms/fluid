<?php
namespace Fluid;

use SimpleXMLElement;

class XmlMappingLoader implements XmlMappingLoaderInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->setConfig($config);
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
     * @param string $dir
     * @return array
     */
    public function filelist($dir)
    {
        $dir = $this->getConfig()->getMapping() . DIRECTORY_SEPARATOR . $dir;
        $retval = [];
        if (is_dir($dir)) {
            foreach (scandir($dir) as $file) {
                if ($file !== '.' && $file !== '..') {
                    $retval[] = $file;
                }
            }
        }
        return $retval;
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