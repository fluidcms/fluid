<?php

namespace Fluid\Page;

use Exception,
    Fluid\Fluid,
    Fluid\Map\Map,
    Fluid\Layout\Layout,
    Fluid\File\Image;

class UpdateData
{
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

            foreach($vars as $name => $info) {
                if (!isset($data[$group][$name])) {
                    $data[$group][$name] = null;
                }

                switch($info['type']) {
                    case 'string':
                        $output[$group][$name] = self::sanitizeString($data[$group][$name]);
                        break;
                    case 'content':
                        $output[$group][$name] = self::sanitizeContent($data[$group][$name]);
                        break;
                    case 'image':
                        if (is_string($data[$group][$name]) && strlen($data[$group][$name]) === 8) {
                            $image = Image::format($data[$group][$name], $info);
                            $output[$group][$name] = self::sanitizeNewImage($image);
                        } else {
                            $output[$group][$name] = self::sanitizeImage($data[$group][$name]);
                        }
                        break;
                }

                // Save universal variables
                if (isset($info['universal']) && $info['universal'] === true) {
                    self::updateUniversal($page, $language, $languages, $group, $name, $output[$group][$name]);
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
    private static function sanitizeString($value)
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
    private static function sanitizeContent($value)
    {
        return $value; // TODO: sanitize maybe?
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