<?php

namespace Fluid\Layout;

use Exception,
    Fluid\Fluid;

/**
 * Layout model
 *
 * @package fluid
 */
class Layout
{
    /**
     * Get languages
     *
     * @return  array
     */
    public static function getLayouts()
    {
        $layouts = array();
        $dir = Fluid::getConfig('templates') . Fluid::getConfig('layouts');

        foreach (scandir($dir) as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            } else if (is_dir($dir.$file) && file_exists("{$dir}{$file}/layout.xml")) {
                $layouts[] = array(
                    'layout' => $file
                );
            }
        }

        return $layouts;
    }

    /**
     * Validate layout
     *
     * @param   array       $value
     * @throws  Exception
     * @return  bool
     */
    public static function validateLayout($value)
    {
        if (!is_string($value) || empty($value)) {
            throw new Exception("Invalid layout");
        }

        $valid = self::getLayouts();
        $found = false;
        foreach($valid as $haystack) {
            if ($haystack['layout'] === $value) {
                $found = true;
                break;
            }
        }

        if (!$found) {
            throw new Exception("Invalid layout");
        }

        return true;
    }}