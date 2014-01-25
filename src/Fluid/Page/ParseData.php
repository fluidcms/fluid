<?php

namespace Fluid\Page;

use Fluid\Fluid;
use Fluid\View;
use Fluid\Layout\Layout;
use Fluid\Component\Component;

class ParseData
{
    private static $language;

    /**
     * Parse json data
     *
     * @param Page $page
     * @param Layout $layout
     * @param string $language
     * @return array
     */
    public static function parse(Page $page, Layout $layout, $language = null)
    {
        if (null === self::$language) {
            if (!empty($language)) {
                self::$language = $language;
            } else if (null !== $page->getLanguage()) {
                self::$language = $page->getLanguage();
            } else {
                self::$language = Fluid::getLanguage();
            }
        }

        $data = $page->getRawData($language);
        $data = self::merge($layout->getDefinition(), $data);

        return $data;
    }

    /**
     * Merge layout definition with page data
     *
     * @param array $layout
     * @param array $data
     * @return array
     */
    private static function merge(array $layout, array $data)
    {
        $output = array();

        foreach ($layout as $group => $vars) {
            $output[$group] = self::parseVariables($vars, (!isset($data[$group]) ? array() : $data[$group]));
        }

        return $output;
    }

    /**
     * Parse variables
     *
     * @param array $variables
     * @param array $data
     * @return array
     */
    private static function parseVariables(array $variables, array $data)
    {
        $retval = array();

        foreach ($variables as $name => $var) {
            switch ($var['type']) {
                case 'string':
                    if (isset($data[$name])) {
                        $retval[$name] = (string)$data[$name];
                    } else {
                        $retval[$name] = '';
                    }
                    break;
                case 'content':
                    if (isset($data[$name]) && is_array($data[$name])) {
                        $retval[$name] = self::renderContent($data[$name]);
                    } elseif (isset($data[$name]) && is_string($data[$name])) {
                        $retval[$name] = self::renderContent([
                            'source' => $data[$name],
                            'components' => null,
                            'images' => null
                        ]);
                    } else {
                        $retval[$name] = '';
                    }
                    break;
                case 'image':
                    if (isset($data[$name]) && is_array($data[$name])) {
                        $retval[$name] = $data[$name];
                    } else {
                        $retval[$name] = array();
                    }
                    break;
                case 'bool':
                    if (isset($data[$name])) {
                        $retval[$name] = (bool)$data[$name];
                    } else {
                        $retval[$name] = false;
                    }
                    break;
                case 'option':
                    if (isset($data[$name])) {
                        $retval[$name] = (string)$data[$name];
                    } else {
                        $retval[$name] = '';
                    }
                    break;
                case 'table':
                    if (isset($data[$name])) {
                        $retval[$name] = $data[$name];
                    } else {
                        $retval[$name] = '';
                    }
                    break;
                case 'array':
                    $retval[$name] = array();
                    if (isset($data[$name]) && is_array($data[$name]) && count($data[$name])) {
                        foreach ($data[$name] as $arrayValue) {
                            $retval[$name][] = self::parseVariables($var['variables'], $arrayValue);
                        }
                    }
                    break;
            }
        }

        return $retval;
    }

    /**
     * Render a content variable
     *
     * @param array $content
     * @return string
     */
    private static function renderContent(array $content)
    {
        $output = $content['source'];

        // Components
        if (is_array($content['components'])) {
            foreach ($content['components'] as $id => $component) {
                $definition = Component::get($component['component']);
                $data = self::parseVariables($definition->getVariables(), $component['data']);

                $file = $definition->getFile();
                $macro = $definition->getMacro();

                $data = array_merge(array('language' => substr(self::$language, 0, 2)), $data);

                if (!empty($macro)) {
                    $html = View::macro($macro, $file, $data);
                } else {
                    $html = View::create($file, $data);
                }

                $output = str_replace('{' . $id . '}', $html, $output);
            }
        }

        // Images
        if (is_array($content['images'])) {
            foreach ($content['images'] as $id => $image) {
                $html = '<img src="' . $image['src'] . '"';
                if (!empty($image['width'])) {
                    $html .= ' width="' . $image['width'] . '"';
                }
                if (!empty($image['height'])) {
                    $html .= ' height="' . $image['height'] . '"';
                }
                if (!empty($image['alt'])) {
                    $html .= ' alt="' . $image['alt'] . '"';
                }
                $html .= '>';

                $output = str_replace('{' . $id . '}', $html, $output);
            }
        }

        return $output;
    }
}
