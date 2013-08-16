<?php

namespace Fluid\Page;

use Fluid\Fluid,
    Fluid\Layout\Layout,
    Fluid\File\Image;

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
                    case 'image':
                        if (is_string($data[$group][$name]) && strlen($data[$group][$name]) === 8) {
                            $image = Image::format($data[$group][$name], $info);
                            $output[$group][$name] = self::newImage($image);
                        } else {
                            $output[$group][$name] = $data[$group][$name]; // TODO: sanitize maybe?
                        }
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
        return $value; // TODO: sanitize maybe?
    }

    /**
     * Sanitize new image data
     *
     * @param   array   $value
     * @return  array
     */
    private static function newImage($value)
    {
        $retval = array();
        $dir = Fluid::getBranchStorage() . "files";

        foreach($value as $name => $format) {
            $image = array(
                "src" => preg_replace("!^{$dir}!", '/fluidcms/images', $format['path']),
                "alt" => "",
                "width" => $format["width"],
                "height" => $format["height"]
            );
            if (empty($name)) {
                $retval = $image;
            } else {
                $retval[$name] = $image;
            }
        }

        return $retval;
    }
}