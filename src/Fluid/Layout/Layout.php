<?php

namespace Fluid\Layout;

use Fluid\Fluid;

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
}