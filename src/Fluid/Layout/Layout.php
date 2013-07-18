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
        $dir = scandir(Fluid::getConfig('templates') . Fluid::getConfig('layouts'));

        foreach ($dir as $file) {
            if (strpos($file, '.twig') !== false) {
                $layouts[] = array(
                    'layout' => str_replace('.twig', '', $file)
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