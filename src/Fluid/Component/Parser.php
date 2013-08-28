<?php

namespace Fluid\Component;

use Fluid\Component\Component, Fluid\Definition\Definition, SimpleXMLElement;

class Parser extends Definition
{
    /**
     * Parse a layout XML file
     *
     * @param   Component  $component
     * @return  Component
     */
    public static function parse(Component $component)
    {
        $xmlFile = $component->getXMLFile();
        $xmlObject = new SimpleXMLElement($xmlFile, null, true);

        $config = self::getConfig($xmlObject);
        $variables = self::getVariables($xmlObject);

        if (isset($config['file'])) {
            $component->setFile($config['file']);
        }

        if (isset($config['icon'])) {
            $component->setIcon($config['icon']);
        }

        if (isset($config['name'])) {
            $component->setName($config['name']);
        }

        $component->setVariables($variables);

        return $component;
    }

}