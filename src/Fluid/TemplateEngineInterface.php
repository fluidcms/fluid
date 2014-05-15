<?php
namespace Fluid;

use Fluid\Template\TemplateConfig;

interface TemplateEngineInterface
{
    /**
     * @param string $template
     * @param array $data
     * @param TemplateConfig $config
     * @return string
     */
    public function render($template, array $data, TemplateConfig $config);

    /**
     * @param string $template
     * @param array $data
     * @param TemplateConfig $config
     * @return string
     */
    public function renderCompontent($template, array $data, TemplateConfig $config);
}