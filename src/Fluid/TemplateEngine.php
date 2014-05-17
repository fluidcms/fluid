<?php
namespace Fluid;

use Fluid\Template\TemplateConfig;

class TemplateEngine implements TemplateEngineInterface
{
    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        $this->setRegistry($registry);
    }

    /**
     * @param string $template
     * @param array $data
     * @param TemplateConfig $config
     * @return string
     */
    public function render($template, array $data, TemplateConfig $config)
    {
        // fixme
        return null;
        $file = Config::get('templates') . '/' . $template;
        if (file_exists($file)) {
            foreach ($data as $key => $value) {
                $GLOBALS[$key] = $value;
            }
            return require $file;
        }
        return null;
    }

    /**
     * @param string $template
     * @param array $data
     * @param TemplateConfig $config
     * @return string
     */
    public function renderCompontent($template, array $data, TemplateConfig $config)
    {
        // fixme
        return null;
        $file = Config::get('templates') . '/' . $template;
        if (file_exists($file)) {
            foreach ($data as $key => $value) {
                $GLOBALS[$key] = $value;
            }
            return require $file;
        }
        return null;
    }

    /**
     * @return RegistryInterface
     */
    public function getRegistry()
    {
        return $this->registry;
    }

    /**
     * @param RegistryInterface $registry
     * @return $this
     */
    public function setRegistry(RegistryInterface $registry)
    {
        $this->registry = $registry;
        return $this;
    }
}