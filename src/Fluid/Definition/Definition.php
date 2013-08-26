<?php

namespace Fluid\Definition;

use SimpleXMLElement;

abstract class Definition
{
    private static $variableTypes = array('string', 'content', 'option', 'components', 'bool', 'date');

    /**
     * Get the configurations
     *
     * @param   SimpleXMLElement    $xml
     * @return  array
     */
    protected static function getConfig(SimpleXMLElement $xml)
    {
        $config = array();

        if (isset($xml->config)) {
            foreach($xml->config->children() as $setting) {
                $settingKey = null;
                foreach($setting->attributes() as $key => $value) {
                    $value = (string)$value;
                    if ($key === 'name') {
                        $settingKey = $value;
                    }

                    else if ($settingKey !== null && $key === 'value') {
                        $config[$settingKey] = $value;
                        $settingKey = null;
                    }
                }
            }
        }

        return $config;
    }

    /**
     * Get the variables
     *
     * @param   SimpleXMLElement    $xml
     * @param   string  $file
     * @return  array
     */
    protected static function getVariablesGroups(SimpleXMLElement $xml, $file)
    {
        $groups = array();
        $extendingGroups = array();

        if (isset($xml->extend)) {
            foreach($xml->extend as $extending) {
                foreach($extending->attributes() as $key => $value) {
                    if ($key === 'file') {
                        $dir = dirname($file);
                        $filePath = realpath($dir . '/' . (string)$value);
                        $fileXmlElement = new SimpleXMLElement($filePath, null, true);
                        $extendingGroups[] = self::getVariablesGroups($fileXmlElement, $filePath);
                    }
                }
            }
        }

        if (isset($xml->group)) {
            foreach($xml->group as $group) {
                foreach($group->attributes() as $key => $value) {
                    if ($key === 'name') {
                        $groupName = (string)$value;
                    }
                }
                if (!empty($groupName)) {
                    $groups[$groupName] = self::getVariables($group);

                }
            }
        }

        foreach($extendingGroups as $extendingGroup) {
            $groups = array_merge($extendingGroup, $groups);
        }

        return $groups;
    }

    /**
     * Parse group items
     *
     * @param   SimpleXMLElement   $xml
     * @param   bool    $child
     * @return  array
     */
    protected static function getVariables(SimpleXMLElement $xml, $child = false)
    {
        $variables = array();

        foreach($xml->children() as $item) {
            $itemName = $item->getName();
            switch($itemName) {
                // Variable
                case 'variable':
                    foreach($item->attributes() as $key => $value) {
                        switch($key) {
                            case 'name': $variableName = (string)$value; break;
                            case 'type': $variableType = (string)$value; break;
                        }
                    }
                    if (!empty($variableName) && !empty($variableType) && in_array($variableType, self::$variableTypes)) {
                        if ($variableType === 'option') {
                            $variableOptions = array();
                            foreach($item->children() as $option) {
                                if ($option->getName() === 'option') {
                                    foreach($option->attributes() as $key => $value) {
                                        if ($key === 'value') {
                                            $variableOptions[] = (string)$value;
                                        }
                                    }
                                }
                            }
                        }

                        $groupItem = array(
                            'type' => $variableType
                        );

                        if (isset($variableOptions)) {
                            $groupItem['options'] = $variableOptions;
                        }

                        $variables[$variableName] = $groupItem;
                    }
                    break;

                // Array
                case 'array':
                    if (!$child) {
                        foreach($item->attributes() as $key => $value) {
                            switch($key) {
                                case 'name': $variableName = (string)$value; break;
                            }
                        }

                        if (!empty($variableName)) {
                            $variables[$variableName] = array(
                                'type' => 'array',
                                'variables' => self::getVariables($item, true)
                            );
                        }
                    }
                    break;

                // Image
                case 'image':
                    $image = array();
                    $image['type'] = 'image';
                    foreach($item->attributes() as $key => $value) {
                        switch($key) {
                            case 'name': $imageName = (string)$value; break;
                            case 'width': $image['width'] = (string)$value; break;
                            case 'height': $image['height'] = (string)$value; break;
                            case 'format': $image['format'] = (string)$value; break;
                            case 'quality': $image['quality'] = (string)$value; break;
                        }
                    }

                    // Formats
                    $formats = array();
                    foreach($item->children() as $format) {
                        if ($format->getName() === 'format') {
                            $thisFormat = array();
                            foreach($format->attributes() as $key => $value) {
                                switch($key) {
                                    case 'name': $formatName = (string)$value; break;
                                    case 'width': $thisFormat['width'] = (string)$value; break;
                                    case 'height': $thisFormat['height'] = (string)$value; break;
                                    case 'format': $thisFormat['format'] = (string)$value; break;
                                    case 'quality': $thisFormat['quality'] = (string)$value; break;
                                }
                            }
                            if (empty($thisFormat['quality']) && !empty($image['quality'])) {
                                $thisFormat['quality'] = $image['quality'];
                            }
                            if (empty($thisFormat['format']) && !empty($image['format'])) {
                                $thisFormat['format'] = $image['format'];
                            }
                            if (!empty($formatName)) {
                                $formats[$formatName] = $thisFormat;
                            }
                        }
                    }

                    if (count($formats)) {
                        $image['formats'] = $formats;
                    }

                    if (!empty($imageName)) {
                        $variables[$imageName] = $image;
                    }

                    break;
            }
        }

        return $variables;
    }
}