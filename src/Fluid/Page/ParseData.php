<?php

namespace Fluid\Page;

use Fluid\Fluid,
    Fluid\Layout\Layout,
    Fluid\Page\Page,
    Fluid\Storage\FileSystem;

class ParseData
{
    /**
     * Parse json data
     *
     * @param   Page    $page
     * @param   Layout  $layout
     * @return  array
     */
    public static function parse(Page $page, Layout $layout)
    {
        $data = $page->getRawData();
        $data = self::merge($layout->getVariables(), $data);

        return;
    }

    /**
     * Merge layout definition with page data
     *
     * @param   array   $layout
     * @param   array   $data
     * @return  array
     */
    private static function merge($layout, $data)
    {
        $output = array();

        foreach($layout as $group => $vars) {
            $output[$group] = self::parseVariables($vars, (!isset($data[$group]) ? array() : $data[$group]));
        }

        // TODO: finish this
        return 'yo';
    }

    /**
     * Parse variables
     *
     * @param   array   $variables
     * @param   array   $data
     * @return  array
     */
    private static function parseVariables($variables, $data)
    {
        $output = array();

        foreach($variables as $name => $var) {
            switch($var['type']) {
                case 'string':
                    if (isset($data[$name])) {
                        $output[$name] = (string)$data[$name];
                    } else {
                        $output[$name] = '';
                    }
                    break;
                case '':
                    // TODO: other types
                    break;
            }
        }

        return $output;
    }
}