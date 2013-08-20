<?php

namespace Fluid\Component;

use Exception,
    Fluid\Fluid;

/**
 * Component model
 *
 * @package fluid
 */
class Component
{
    private $component;
    private $xmlFile;
    private $name;
    private $file;
    private $icon;
    private $variables;

    /**
     * Component
     *
     * @param   string  $component
     * @throws  Exception
     */
    public function __construct($component)
    {
        $dir = Fluid::getConfig('templates') . Fluid::getConfig('components');
        $file = "{$dir}{$component}/component.xml";

        if (!is_file($file)) {
            throw new Exception("Invalid component");
        }

        $this->component = $component;
        $this->xmlFile = $file;

        Parser::parse($this);
    }

    /**
     * Set file
     *
     * @param   string  $value
     * @return  void
     */
    public function setFile($value)
    {
        $this->file = (string)$value;
    }

    /**
     * Set name
     *
     * @param   string  $value
     * @return  void
     */
    public function setName($value)
    {
        $this->name = (string)$value;
    }

    /**
     * Set icon
     *
     * @param   string  $value
     * @return  void
     */
    public function setIcon($value)
    {
        $this->icon = (string)$value;
    }

    /**
     * Set Variables
     *
     * @param   string  $value
     * @return  void
     */
    public function setVariables($value)
    {
        $this->variables = $value;
    }

    /**
     * Set Variables
     *
     * @return  array
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * Get XML file
     *
     * @return  string
     */
    public function getXMLFile()
    {
        return $this->xmlFile;
    }

    /**
     * Return object as array for JSON
     *
     * @return  array
     */
    public function toJSON()
    {
        $icon = dirname($this->xmlFile) . "/" . $this->icon;
        if (file_exists($icon)) {
            $icon = base64_encode(file_get_contents($icon));
        } else {
            $icon = null;
        }

        return array(
            'component' => $this->component,
            'name' => $this->name,
            'icon' => $icon,
            'variables' => $this->variables
        );
    }

    /**
     * Get components list
     *
     * @return  array
     */
    public static function getComponents()
    {
        $components = array();
        $dir = Fluid::getConfig('templates') . Fluid::getConfig('components');

        if (is_dir($dir)) {
            foreach (scandir($dir) as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                } else if (is_dir($dir.$file) && file_exists("{$dir}{$file}/component.xml")) {
                    $components[] = new self($file);
                }
            }
        }

        $output = array();
        foreach($components as $component) {
            $output[] = $component->toJSON();
        }

        return $output;
    }

}