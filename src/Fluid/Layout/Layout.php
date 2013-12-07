<?php
namespace Fluid\Layout;

use Exception;
use DOMDocument;
use DOMElement;
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
        $dir = Config::get('templates') . '/' . Config::get('layouts');

        if ($layout !== 'global') {
            $file = "{$dir}/{$layout}/layout.xml";
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
     * @param string|null $dir
     * @return array
     */
    public static function getLayouts($dir = null)
    {
        $layouts = array();
        $origin = Config::get('configs') . '/layouts';

        if (null === $dir) {
            $dir = $origin;
        }

        if (is_dir($dir)) {
            foreach (scandir($dir) as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                } elseif (is_dir("{$dir}/{$file}")) {
                    $layouts = array_merge($layouts, self::getLayouts("{$dir}/{$file}"));
                } elseif (is_file("{$dir}/{$file}") && substr($file, strlen($file)-4) === '.xml') {
                    $dom = new DOMDocument;
                    $dom->loadXML(file_get_contents("{$dir}/{$file}"));
                    if ($dom->documentElement->tagName === 'fluid-layout') {

                        $layoutName = null;
                        /** @var \DOMElement $element */
                        foreach($dom->getElementsByTagName('config') as $element) {
                            if ($element->hasChildNodes()) {
                                foreach($element->childNodes as $setting) {
                                    if ($setting instanceof DOMElement && $setting->tagName === 'setting') {
                                        $name = $setting->getAttribute('name');
                                        $value = $setting->getAttribute('value');
                                        if ($name === 'name') {
                                            $layoutName = $value;
                                            break;
                                        }
                                    }
                                }
                            }
                        }

                        $layout = substr("{$dir}/{$file}", strlen($origin) + 1, -4);
                        $layouts[] = array(
                            'layout' => $layout,
                            'name' => $layoutName !== null ? $layoutName : $layout
                        );
                    }
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