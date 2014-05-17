<?php
namespace Fluid\Page\Renderer;

use Fluid\Page\PageEntity;
use Fluid\RegistryInterface;

class RenderPage implements RendererInterface
{
    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @var PageEntity
     */
    private $page;

    /**
     * @param RegistryInterface $registry
     * @param PageEntity $page
     */
    public function __construct(RegistryInterface $registry, PageEntity $page)
    {
        $this->setRegistry($registry);
        $this->setPage($page);
    }

    /**
     * @return string
     */
    public function render()
    {
        return $this->getRegistry()->getTemplateEngine()->render($this->page->getTemplate()->getFile(), $this->page->toArray(), $this->page->getTemplate()->getConfig());
    }

    /**
     * @return PageEntity
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param PageEntity $page
     * @return $this
     */
    public function setPage(PageEntity $page)
    {
        $this->page = $page;
        return $this;
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