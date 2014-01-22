<?php

namespace Fluid\Layout;

use Fluid\Definition\Definition;
use SimpleXMLElement;

class Parser extends Definition
{
    /**
     * Parse a layout XML file
     *
     * @param Layout $layout
     * @return Layout
     */
    public static function parse(Layout $layout)
    {
        $xmlFile = $layout->getXMLFile();
        $xmlObject = new SimpleXMLElement($xmlFile, null, true);

        $layout->setConfig(new Config(self::getConfig($xmlObject)));
        $layout->setDefinition(self::getVariablesGroups($xmlObject, $xmlFile));
        return $layout;
    }

}