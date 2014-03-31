<?php
namespace Fluid\Layout;

use Exception;
use DOMDocument;
use DOMElement;
use Fluid\Layout\Config;

/**
 * Layout model
 *
 * @package fluid
 */
class Layout
{
    /** @var string $xmlFile */
    private $xmlFile;

    /** @var array $definition */
    private $definition;

    /** @var \Fluid\Layout\Config $config */
    private $config;

    /**
     * Layout
     *
     * @param string $layout
     * @throws Exception
     */
    public function __construct($layout)
    {
        $xmlFile = \Fluid\Config::get('configs') . "/layouts/{$layout}.xml";
        if (!file_exists($xmlFile)) {
            throw new InvalidLayoutException();
        }
        $this->setXmlFile($xmlFile);
    }

    /**
     * Get a layout
     *
     * @param string $layout
     * @deprecated
     * @return self
     */
    public static function get($layout)
    {
        return new self($layout);
    }

    /**
     * Set layout definition
     *
     * @param array $definition
     * @return $this
     */
    public function setDefinition($definition)
    {
        $this->definition = $definition;
        return $this;
    }

    /**
     * Get layout definition
     *
     * @return array
     */
    public function getDefinition()
    {
        if (null === $this->definition) {
            Parser::parse($this);
        }
        return $this->definition;
    }

    /**
     * @param \Fluid\Layout\Config $config
     * @return $this
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @return \Fluid\Layout\Config
     */
    public function getConfig()
    {
        if (null === $this->config) {
            Parser::parse($this);
        }
        return $this->config;
    }

    /**
     * @param string $xmlFile
     * @return $this
     */
    public function setXmlFile($xmlFile)
    {
        $this->xmlFile = $xmlFile;
        return $this;
    }

    /**
     * @return string
     */
    public function getXmlFile()
    {
        return $this->xmlFile;
    }

    /**
     * Get layouts
     *
     * @param string|null $dir
     * @return array
     * TODO move to repository
     */
    public static function getLayouts($dir = null)
    {
        $layouts = array();
        $origin = \Fluid\Config::get('configs') . '/layouts';

        if (null === $dir) {
            $dir = $origin;
        }

        if (is_dir($dir)) {
            foreach (scandir($dir) as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                } elseif (is_dir("{$dir}/{$file}")) {
                    $layouts = array_merge($layouts, self::getLayouts("{$dir}/{$file}"));
                } elseif (is_file("{$dir}/{$file}") && substr($file, strlen($file) - 4) === '.xml') {
                    $dom = new DOMDocument;
                    $dom->loadXML(file_get_contents("{$dir}/{$file}"));
                    if ($dom->documentElement->tagName === 'fluid-layout') {

                        $layoutName = null;
                        /** @var \DOMElement $element */
                        foreach ($dom->getElementsByTagName('config') as $element) {
                            if ($element->hasChildNodes()) {
                                foreach ($element->childNodes as $setting) {
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
     * @param   array $value
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
        foreach ($valid as $haystack) {
            if ($haystack['layout'] === $value) {
                $found = true;
                break;
            }
        }

        if (!$found) {
            throw new Exception("Invalid layout");
        }

        return true;
    }
}