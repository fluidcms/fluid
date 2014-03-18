<?php
namespace Fluid;

interface XmlMappingLoaderInterface
{
    /**
     * @param string $filename
     * @return MappingInterface
     */
    public function load($filename);
}