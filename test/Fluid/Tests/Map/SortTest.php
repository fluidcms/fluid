<?php

namespace Fluid\Tests\Map;

use Fluid, PHPUnit_Framework_TestCase, Fluid\Tests\Helper;

class SortTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Helper::copyStorage();
    }

    public function testSort()
    {
        $request = array(
            "method" => "PUT",
            "url" => "develop/map/sort/home%20page%2Fproducts",
            "data" => array(
                "page" => "",
                "index" => 1
            )
        );

        Fluid\ManagerRouter::route($request['url'], $request['method'], $request['data']);

        $map = new Fluid\Map\Map;
        $pages = $map->getPages();
        $this->assertEquals('products', $pages[1]['page']);
        $this->assertFileExists(Helper::getStorage() . "/pages/products_en-US.json");
        $this->assertTrue(is_dir(Helper::getStorage() . "/pages/products/"));
    }

    public function tearDown()
    {
        Helper::deleteStorage();
    }
}
