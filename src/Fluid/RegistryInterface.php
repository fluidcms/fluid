<?php
namespace Fluid;

interface RegistryInterface
{
    /**
     * @return StorageInterface
     */
    public function getStorage();

    /**
     * @param StorageInterface $storage
     * @return $this
     */
    public function setStorage(StorageInterface $storage);

    /**
     * @return XmlMappingLoaderInterface
     */
    public function getXmlMappingLoader();

    /**
     * @param XmlMappingLoaderInterface $xmlMappingLoader
     * @return $this
     */
    public function setXmlMappingLoader(XmlMappingLoaderInterface $xmlMappingLoader);

    /**
     * @param TemplateEngineInterface $templateEngine
     * @return $this
     */
    public function setTemplateEngine(TemplateEngineInterface $templateEngine);

    /**
     * @return TemplateEngineInterface
     */
    public function getTemplateEngine();
}