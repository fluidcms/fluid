<?php
namespace Fluid;

use Fluid\Component\ComponentEntity;
use Fluid\Page\PageEntity;

interface TemplateEngineInterface
{
    /**
     * @param PageEntity $page
     * @return string
     */
    public function render(PageEntity $page);

    /**
     * @param ComponentEntity $component
     * @return string
     */
    public function renderCompontent(ComponentEntity $component);
}