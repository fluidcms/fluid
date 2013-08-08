<?php

namespace Fluid\Page;

use Fluid\Layout\Layout;

class SanitizeData
{
    /**
     * Sanitize page data input
     *
     * @param   array   $data
     * @param   Layout  $layout
     * @return  array
     */
    public static function sanitize($data, Layout $layout)
    {
        $output = array();
        $definition = $layout->getVariables();

        foreach($definition as $group => $vars) {
            if (!isset($data[$group])) {
                $data[$group] = array();
            }
            $output[$group] = array();

            foreach($vars as $name => $info) {
                if (!isset($data[$group][$name])) {
                    $data[$group][$name] = null;
                }

                switch($info['type']) {
                    case 'string':
                        $output[$group][$name] = self::string($data[$group][$name]);
                        break;
                    case 'content':
                        $output[$group][$name] = self::content($data[$group][$name]);
                        break;
                }
            }

        }

        return $output;
    }

    /**
     * Sanitize a string
     *
     * @param   string  $value
     * @return  string
     */
    private static function string($value)
    {
        $value = str_replace(array('\n', PHP_EOL), '', $value);
        $value = trim($value);

        return $value;
    }

    /**
     * Sanitize a content
     *
     * @param   array   $value
     * @return  array
     */
    private static function content($value)
    {
        return $value;
    }
}