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

        $config = self::getConfig($xmlObject);
        $variables = self::getVariablesGroups($xmlObject, $xmlFile);

        if (isset($config['file'])) {
            $layout->setFile($config['file']);
        }

        $layout->setVariables($variables);

        return $layout;
    }

}