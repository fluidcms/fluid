<?php

namespace Fluid\Tests\Page;

use Fluid, PHPUnit_Framework_TestCase, Fluid\Tests\Helper;

class GetPageDataTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Helper::copyStorage();
    }

    public function testGetPageData()
    {
        $data = Fluid\Data::get('home page/products/awesome');

        $this->assertEquals('My Website', $data['global']['Site']['Name']);
        $this->assertEquals('home page/products/awesome', $data['page']['id']);
        $this->assertEquals('', $data['page']['Header']['Title']);
        $this->assertEquals('Welcome', $data['parent']['parent']['Content']['Title']);
        $this->assertRegExp('/<img src="/', $data['parent']['parent']['Content']['Content']);

        // Test components
        $data = Fluid\Data::get('home page');
        $this->assertRegExp('/<a>Hello World<\/a>/', $data['page']['Content']['Content']);
    }

    public function tearDown()
    {
        Helper::deleteStorage();
    }
}
