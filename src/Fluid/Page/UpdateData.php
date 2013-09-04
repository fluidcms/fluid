<?php

namespace Fluid\Page;

use Exception,
    Fluid\Fluid,
    Fluid\Map\Map,
    Fluid\Layout\Layout,
    Fluid\Component\Component,
    Fluid\File\Image;

class UpdateData
{
    private static $components = array();

    /**
     * Sanitize page data input
     *
     * @param   Page    $page
     * @param   array   $data
     * @throws  Exception
     * @return  array
     */
    public static function update(Page $page, $data)
    {
        $id = $page->getId();

        $language = $page->getLanguage();

        $languages = $page->getLanguages();

        if (!empty($id)) {
            $layout = new Layout($page->getLayout());
        } else {
            $layout = new Layout('global');
        }

        if (!$layout instanceof Layout) {
            throw new Exception('Invalid page');
        }

        $definition = $layout->getDefinition();

        $data = self::parse($page, $data, $definition, $language, $languages);
        self::save($page, $language, $data);
    }

    /**
     * Save the data to the file
     *
     * @param   Page    $page
     * @param   string  $language
     * @param   array   $data
     * @return  void
     */
    public static function save(Page $page, $language, $data)
    {
        $id = $page->getId();

        $data = json_encode($data, JSON_PRETTY_PRINT);

        if (!empty($id)) {
            $file = 'pages/' . $id . '_' . $language . '.json';
        } else {
            $file = 'global_' . $language . '.json';
        }

        $page::save($data, $file);
    }

    /**
     * Sanitize page data input
     *
     * @param   Page    $page
     * @param   string  $language
     * @param   array   $languages
     * @param   string  $group
     * @param   string  $name
     * @param   mixed   $value
     * @return  void
     */
    private static function updateUniversal(Page $page, $language, $languages, $group, $name, $value)
    {
        foreach($languages as $otherLanguage) {
            if ($otherLanguage !== $language) {
                $data = $page->getRawData($otherLanguage);
                $data[$group][$name] = $value;
                self::save($page, $otherLanguage, $data);
            }
        }
    }

    /**
     * Sanitize page data input
     *
     * @param   Page    $page
     * @param   array   $data
     * @param   array   $definition
     * @param   string  $language
     * @param   array   $languages
     * @return  array
     */
    private static function parse(Page $page, $data, $definition, $language, $languages)
    {
        $output = array();

        foreach($definition as $group => $vars) {
            if (!isset($data[$group])) {
                $data[$group] = array();
            }
            $output[$group] = array();

            foreach($vars as $name => $variable) {
                $output[$group][$name] = self::parseVariable($variable, (!empty($data[$group][$name]) ? $data[$group][$name] : null));

                // Save universal variables
                if (isset($variable['universal']) && $variable['universal'] === true) {
                    self::updateUniversal($page, $language, $languages, $group, $name, $output[$group][$name]);
                }
            }

        }

        return $output;
    }

    /**
     * Parse a variable
     *
     * @param   array   $variable
     * @param   mixed   $value
     * @return  mixed
     */
    private static function parseVariable($variable, $value = null)
    {
        $retval = null;

        switch($variable['type']) {
            case 'string':
                if (!empty($value)) {
                    $retval = self::sanitizeString($value);
                } else {
                    $retval = "";
                }
                break;
            case 'content':
                if (!empty($value)) {
                    $retval = self::sanitizeContent($value);
                } else {
                    $retval = array('source' => '', 'components' => null, 'images' => null);
                }
                break;
            case 'image':
                if (is_string($value) && strlen($value) === 8) {
                    $image = Image::format($value, $variable);
                    $retval = self::sanitizeNewImage($image);
                } else {
                    $retval = self::sanitizeImage($value);
                }
                break;
            case 'array':
                $retval = array();
                foreach($value as $array) {
                    $retArray = array();
                    foreach($variable['variables'] as $name => $arrayVar) {
                        $retArray[$name] = self::parseVariable($arrayVar, (!empty($array[$name]) ? $array[$name] : null));
                    }
                    $retval[] = $retArray;
                }
        }

        return $retval;
    }

    /**
     * Sanitize a string
     *
     * @param   string  $value
     * @return  string
     */
    private static function sanitizeString($value)
    {
        $value = str_replace(array('\n', PHP_EOL), '', $value);
        $value = str_replace('&nbsp;', ' ', $value);
        $value = trim($value);

        return $value;
    }

    /**
     * Sanitize a content
     *
     * @param   array   $value
     * @return  array
     */
    private static function sanitizeContent($value)
    {
        $output = array(
            'source' => $value['source'],
            'components' => array(),
            'images' => array()
        );

        // Sanitize components
        if (isset($value['components']) && is_array($value['components'])) {
            foreach($value['components'] as $id => $component) {
                if (strpos($output['source'], '{'.$id.'}') !== false) {
                    if ($component = self::sanitizeComponent($component)) {
                        $output['components'][$id] = $component;
                    }
                }
            }
        }

        // TODO: sanitize images maybe?
        $output['images'] = $value["images"];

        return $output;
    }

    /**
     * Sanitize component
     *
     * @param   array   $component
     * @return  array
     */
    private static function sanitizeComponent($component)
    {
        try {
            if (!isset(self::$components[$component['component']])) {
                $definition = self::$components[$component['component']] = Component::get($component['component']);
            } else {
                $definition = self::$components[$component['component']];
            }
        } catch(Exception $e) {
            return false;
        }

        $retval = array(
            'component' => $component['component'],
            'data' => array()
        );

        foreach($definition->getVariables() as $name => $variable) {
            $retval['data'][$name] = self::parseVariable($variable, (!empty($component['data'][$name]) ? $component['data'][$name] : null));
        }

        return $retval;
    }

    /**
     * Sanitize image data
     *
     * @param   array   $value
     * @return  array
     */
    private static function sanitizeImage($value)
    {
        return $value; // TODO: sanitize maybe?
    }

    /**
     * Sanitize new image data
     *
     * @param   array   $value
     * @return  array
     */
    private static function sanitizeNewImage($value)
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