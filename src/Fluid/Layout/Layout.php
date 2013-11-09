<?php

namespace Fluid\Layout;

use Exception;
use Fluid\Fluid;
use Fluid\Config;

/**
 * Layout model
 *
 * @package fluid
 */
class Layout
{
    private $xmlFile;
    private $file;
    private $definition;

    /**
     * Layout
     *
     * @param   string  $layout
     * @throws  Exception
     */
    public function __construct($layout)
    {
        $dir = Config::get('templates') . Config::get('layouts');

        if ($layout !== 'global') {
            $file = "{$dir}{$layout}/layout.xml";
        } else {
            $file = "{$dir}/global.xml";
        }

        if (!is_file($file)) {
            throw new Exception("Invalid layout");
        }

        $this->xmlFile = $file;

        Parser::parse($this);
    }

    /**
     * Get a layout
     *
     * @param   string  $layout
     * @return  self
     */
    public static function get($layout)
    {
        return new self($layout);
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
     * Set file
     *
     * @param   string  $value
     * @return  void
     */
    public function setFile($value)
    {
        $this->file = $value;
    }

    /**
     * Get file
     *
     * @return  string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set layout definition
     *
     * @param   string  $value
     * @return  void
     */
    public function setDefinition($value)
    {
        $this->definition = $value;
    }

    /**
     * Set Variables
     *
     * @param   string  $value
     * @deprecated  use setDefinition instead
     * @return  void
     */
    public function setVariables($value)
    {
        $this->setDefinition($value);
    }

    /**
     * Get layout definition
     *
     * @return  array
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * Get Variables
     *
     * @deprecated  use getDefinition instead
     * @return  array
     */
    public function getVariables()
    {
        return $this->getDefinition();
    }

    /**
     * Get layouts
     *
     * @return  array
     */
    public static function getLayouts()
    {
        $layouts = array();
        $dir = Config::get('templates') . Config::get('layouts');

        if (is_dir($dir)) {
            foreach (scandir($dir) as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                } else if (is_dir($dir.$file) && file_exists("{$dir}{$file}/layout.xml")) {
                    $layouts[] = array(
                        'layout' => $file
                    );
                }
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