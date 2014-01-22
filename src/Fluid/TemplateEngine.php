<?php
namespace Fluid;

class TemplateEngine implements TemplateEngineInterface
{
    /**
     * @param string $template
     * @param array $data
     * @param \Fluid\Layout\Config $config
     * @return string
     */
    public function render($template, array $data, Layout\Config $config)
    {
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
     * @param \Fluid\Layout\Config $config
     * @return string
     */
    public function renderCompontent($template, array $data, Layout\Config $config)
    {
        $file = Config::get('templates') . '/' . $template;
        if (file_exists($file)) {
            foreach ($data as $key => $value) {
                $GLOBALS[$key] = $value;
            }
            return require $file;
        }
        return null;
    }
}