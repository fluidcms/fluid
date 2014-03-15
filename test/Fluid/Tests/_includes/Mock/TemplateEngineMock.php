<?php
namespace Fluid\Tests\Mock;

use Fluid\TemplateEngineInterface;
use Fluid\Layout;

class TemplateEngineMock implements TemplateEngineInterface
{
    public function render($template, array $data, Layout\Config $config)
    {
    }

    public function renderCompontent($template, array $data, Layout\Config $config)
    {
    }
}