<?php
namespace Fluid\Mapping;

use Exception;
use SimpleXMLElement;

class VariableMapping
{
    /**
     * @var array
     */
    private $variableTypes = ['string', 'content', 'option', 'components', 'bool', 'date'];

    /**
     * Get the configurations
     *
     * @param SimpleXMLElement $xml
     * @return array
     */
    protected static function getConfig(SimpleXMLElement $xml)
    {
        $config = array();

        if (isset($xml->config)) {
            foreach ($xml->config->children() as $setting) {
                $settingKey = null;
                foreach ($setting->attributes() as $key => $value) {
                    $value = (string)$value;
                    if ($key === 'name') {
                        $settingKey = $value;
                    } else if ($settingKey !== null && $key === 'value') {
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
     * @param SimpleXMLElement $xml
     * @param string $file
     * @throws \Exception
     * @return array
     */
    protected static function getVariablesGroups(SimpleXMLElement $xml, $file)
    {
        $groups = array();
        $extendingGroups = array();

        if (isset($xml->extend)) {
            foreach ($xml->extend as $extending) {
                foreach ($extending->attributes() as $key => $value) {
                    if ($key === 'file') {
                        $dir = dirname($file);
                        $filePath = realpath($dir . '/' . (string)$value);
                        if (!file_exists($filePath)) {
                            throw new Exception('Layout file: ' . $dir . '/' . (string)$value . ' does not exists');
                        }
                        $fileXmlElement = new SimpleXMLElement($filePath, null, true);
                        $extendingGroups[] = self::getVariablesGroups($fileXmlElement, $filePath);
                    }
                }
            }
        }

        if (isset($xml->group)) {
            foreach ($xml->group as $group) {
                $universal = false;
                foreach ($group->attributes() as $key => $value) {
                    if ($key === 'name') {
                        $groupName = (string)$value;
                    }

                    if ($key === 'universal' && ((string)$value === 'true' || (string)$value === '1')) {
                        $universal = true;
                    }
                }
                if (!empty($groupName)) {
                    $groups[$groupName] = self::getVariables($group, false, $universal);

                }
            }
        }

        foreach ($extendingGroups as $extendingGroup) {
            $groups = array_merge($extendingGroup, $groups);
        }

        return $groups;
    }

    /**
     * Parse group items
     *
     * @param SimpleXMLElement $xml
     * @param bool $child
     * @param bool $universal
     * @return array
     */
    protected static function getVariables(SimpleXMLElement $xml, $child = false, $universal = false)
    {
        $variables = array();

        foreach ($xml->children() as $item) {
            $itemName = $item->getName();
            switch ($itemName) {
                // Variable
                case 'variable':
                    // TODO: use an array here because variables can overlap with the previous item unless they are unset
                    $variableUniversal = $universal;
                    foreach ($item->attributes() as $key => $value) {
                        switch ($key) {
                            case 'name':
                                $variableName = (string)$value;
                                break;
                            case 'type':
                                $variableType = (string)$value;
                                break;
                            case 'universal':
                                if ((string)$value === 'true' || (string)$value === '1') {
                                    $variableUniversal = true;
                                }
                                break;
                        }
                    }
                    if (!empty($variableName) && !empty($variableType) && in_array($variableType, self::$variableTypes)) {
                        if ($variableType === 'option') {
                            $variableOptions = array();
                            foreach ($item->children() as $option) {
                                if ($option->getName() === 'option') {
                                    $name = '';
                                    $value = '';
                                    foreach ($option->attributes() as $key => $value) {
                                        if ($key === 'value') {
                                            $value = (string)$value;
                                        }
                                    }
                                    $name = (string)$option;
                                    if ($name === '' && $value !== '') {
                                        $name = $value;
                                    } else if ($name !== '' && $value === '') {
                                        $value = $name;
                                    }
                                    $variableOptions[] = array('name' => $name, 'value' => $value);
                                }
                            }
                        }

                        $groupItem = array(
                            'type' => $variableType,
                            'universal' => $variableUniversal
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
                        // TODO: use an array here because variables can overlap with the previous item unless they are unset
                        $variableUniversal = $universal;
                        foreach ($item->attributes() as $key => $value) {
                            switch ($key) {
                                case 'name':
                                    $variableName = (string)$value;
                                    break;
                                case 'universal':
                                    if ((string)$value === 'true' || (string)$value === '1') {
                                        $variableUniversal = true;
                                    }
                                    break;
                            }
                        }

                        if (!empty($variableName)) {
                            $variables[$variableName] = array(
                                'type' => 'array',
                                'variables' => self::getVariables($item, true, $variableUniversal)
                            );
                        }
                    }
                    break;

                // Table
                case 'table':
                    $table = array(
                        'type' => 'table',
                        'header' => false,
                        'footer' => false,
                        'universal' => $universal
                    );
                    foreach ($item->attributes() as $key => $value) {
                        switch ($key) {
                            case 'name':
                                $table['name'] = (string)$value;
                                break;
                            case 'header':
                                if ((string)$value === 'true' || (string)$value === '1') {
                                    $table['header'] = true;
                                }
                                break;
                            case 'footer':
                                if ((string)$value === 'true' || (string)$value === '1') {
                                    $table['footer'] = true;
                                }
                                break;
                            case 'columns':
                                $table['columns'] = (int)$value;
                                break;
                            case 'rows':
                                $table['rows'] = (int)$value;
                                break;
                            case 'universal':
                                if ((string)$value === 'true' || (string)$value === '1') {
                                    $table['universal'] = true;
                                }
                                break;
                        }
                    }
                    if (!empty($table['name'])) {
                        $variables[$table['name']] = $table;
                    }
                    break;

                // Image
                case 'image':
                    $image = array();
                    $image['type'] = 'image';
                    // TODO: use an array here because variables can overlap with the previous item unless they are unset
                    // TODO: for variableUniversal and imageName in this case
                    $variableUniversal = $universal;
                    foreach ($item->attributes() as $key => $value) {
                        switch ($key) {
                            case 'name':
                                $imageName = (string)$value;
                                break;
                            case 'width':
                                $image['width'] = (string)$value;
                                break;
                            case 'height':
                                $image['height'] = (string)$value;
                                break;
                            case 'format':
                                $image['format'] = (string)$value;
                                break;
                            case 'quality':
                                $image['quality'] = (string)$value;
                                break;
                            case 'universal':
                                if ((string)$value === 'true' || (string)$value === '1') {
                                    $variableUniversal = true;
                                }
                                break;
                        }
                    }
                    $image['universal'] = $variableUniversal;

                    // Formats
                    $formats = array();
                    foreach ($item->children() as $format) {
                        if ($format->getName() === 'format') {
                            $thisFormat = array();
                            foreach ($format->attributes() as $key => $value) {
                                switch ($key) {
                                    case 'name':
                                        $formatName = (string)$value;
                                        break;
                                    case 'width':
                                        $thisFormat['width'] = (string)$value;
                                        break;
                                    case 'height':
                                        $thisFormat['height'] = (string)$value;
                                        break;
                                    case 'format':
                                        $thisFormat['format'] = (string)$value;
                                        break;
                                    case 'quality':
                                        $thisFormat['quality'] = (string)$value;
                                        break;
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