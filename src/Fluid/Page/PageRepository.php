<?php
namespace Fluid\Page;

/**
 * Class PageRepository
 * @package Fluid\Page
 */
class PageRepository
{
    /**
     * @var array
     */
    private $pages = [];

    /**
     * @param array $pages
     */
    public function __construct(array $pages = [])
    {
        foreach ($pages as $page) {
            $this->pages[] = new PageEntity($page);
        }
    }

    /**
     * @param $name
     * @return PageEntity
     */
    public function find($name)
    {

    }
}