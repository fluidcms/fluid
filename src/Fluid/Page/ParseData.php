<?php

namespace Fluid\Page;

use Fluid\Fluid,
    Fluid\View,
    Fluid\Layout\Layout,
    Fluid\Component\Component,
    Fluid\Page\Page,
    Fluid\Storage\FileSystem;

class ParseData
{
    /**
     * Parse json data
     *
     * @param   Page    $page
     * @param   Layout  $layout
     * @param   string  $language
     * @return  array
     */
    public static function parse(Page $page, Layout $layout, $language = null)
    {
        $data = $page->getRawData($language);
        $data = self::merge($layout->getDefinition(), $data);

        return $data;
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

        return $output;
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
                case 'content':
                    if (isset($data[$name])) {
                        $output[$name] = self::renderContent($data[$name]);
                    } else {
                        $output[$name] = '';
                    }
                    break;
                case 'image':
                    if (isset($data[$name]) && is_array($data[$name])) {
                        $output[$name] = $data[$name];
                    } else {
                        $output[$name] = array();
                    }
                    break;
            }
        }

        return $output;
    }

    /**
     * Render a content variable
     *
     * @param   array   $content
     * @return  string
     */
    private static function renderContent($content)
    {
        $output = $content['source'];

        // Components
        foreach($content['components'] as $id => $component) {
            $definition = Component::get($component['component']);
            $data = self::parseVariables($definition->getVariables(), $component['data']);

            $templates = View::getTemplatesDir();
            $file = substr($definition->getFile(), strlen($templates)-1);
            $macro = $definition->getMacro();

            if (!empty($macro)) {
                $html = View::macro($macro, $file, $data);
            } else {
                $html = View::create($file, $data);
            }

            $output = str_replace('{'.$id.'}', $html, $output);
        }

        // Images
        foreach($content['images'] as $id => $image) {
            $html = '<img src="'.$image['src'].'"';
            if (!empty($image['width'])) {
                $html .= ' width="'.$image['width'].'"';
            }
            if (!empty($image['height'])) {
                $html .= ' height="'.$image['height'].'"';
            }
            if (!empty($image['alt'])) {
                $html .= ' alt="'.$image['alt'].'"';
            }
            $html .= '>';

            $output = str_replace('{'.$id.'}', $html, $output);
        }

        return $output;
    }
}
