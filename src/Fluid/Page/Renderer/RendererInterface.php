<?php
namespace Fluid\Page\Renderer;

use Fluid\Page\PageEntity;

interface RendererInterface
{
    /**
     * @return string
     */
    public function render();

    /**
     * @return PageEntity
     */
    public function getPage();

    /**
     * @param PageEntity $page
     * @return $this
     */
    public function setPage(PageEntity $page);
}