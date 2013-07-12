<?php

namespace Fluid\Tests\Map;

use Fluid, PHPUnit_Framework_TestCase, Fluid\Tests\Helper;

class SortTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Helper::copyStorage();
    }

    public function testSortMap()
    {
        ob_start();
        // Test sub page to root
        $request = array(
            "method" => "PUT",
            "url" => "map/sort/home%20page%2Fproducts",
            "data" => array(
                "page" => "",
                "index" => 1
            )
        );

        new Fluid\WebSockets\Requests($request['url'], $request['method'], $request['data'], 'develop', Helper::getUser());

        $map = new Fluid\Map\Map;
        $pages = $map->getPages();
        $this->assertEquals('products', $pages[1]['page']);
        $this->assertFileExists(Helper::getStorage() . "/pages/products_en-US.json");
        $this->assertTrue(is_dir(Helper::getStorage() . "/pages/products/"));

        // Test root page to root
        $request = array(
            "method" => "PUT",
            "url" => "map/sort/home%20page",
            "data" => array(
                "page" => "",
                "index" => 1
            )
        );

        new Fluid\WebSockets\Requests($request['url'], $request['method'], $request['data'], 'develop', Helper::getUser());

        $map = new Fluid\Map\Map;
        $pages = $map->getPages();
        $this->assertEquals('home page', $pages[1]['page']);

        // Test root page to subpage
        $request = array(
            "method" => "PUT",
            "url" => "map/sort/home%20page",
            "data" => array(
                "page" => "products",
                "index" => 0
            )
        );

        new Fluid\WebSockets\Requests($request['url'], $request['method'], $request['data'], 'develop', Helper::getUser());

        $map = new Fluid\Map\Map;
        $pages = $map->getPages();
        $this->assertEquals('home page', $pages[0]['pages'][0]['page']);

        ob_end_clean();
    }

    public function tearDown()
    {
        Helper::deleteStorage();
    }
}
