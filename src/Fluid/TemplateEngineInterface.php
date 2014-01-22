<?php
namespace Fluid;

interface TemplateEngineInterface
{
    /**
     * @param string $template
     * @param array $data
     * @param \Fluid\Layout\Config $config
     * @return string
     */
    public function render($template, array $data, Layout\Config $config);

    /**
     * @param string $template
     * @param array $data
     * @param \Fluid\Layout\Config $config
     * @return string
     */
    public function renderCompontent($template, array $data, Layout\Config $config);
}