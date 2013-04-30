<?php

namespace Fluid\Tests\Models;

use Fluid\Fluid, Fluid\Models\Structure, Fluid\Models\Page, PHPUnit_Framework_TestCase;

class PageTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $_POST = require __DIR__ . "/../Fixtures/request/page_request.php";
    }

    public function testInit()
    {
        $structure = new Structure();

        $page = new Page($structure, 'contact/form');

        return $page;
    }

    /**
     * @depends testInit
     */
    public function testPageParent(Page $page)
    {
        $this->assertTrue($page->hasParent());
        $this->assertInstanceOf('Fluid\Models\Page', $page->parent);
        $this->assertFalse($page->parent->hasParent());
    }

    public function testMergeTemplateData()
    {
        $data = Page::mergeTemplateData($_POST['content']);

        $this->assertArrayHasKey('main_content', $data['page']->data);
    }

    public function tearDown()
    {
        unset($_POST);
    }
}
