<?php

namespace Fluid\Tests\Map;

use Fluid, PHPUnit_Framework_TestCase, Fluid\Tests\Helper;

class DeleteTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Helper::copyStorage();
    }

    public function testDeleteMap()
    {
        $request = array(
            "method" => "DELETE",
            "url" => "map/home%20page%2Fproducts",
            "data" => array()
        );

        ob_start();
        new Fluid\Requests\WebSocket($request['url'], $request['method'], $request['data'], 'develop', Helper::getUser());
        ob_end_clean();

        $map = new Fluid\Map\Map;
        $pages = $map->getPages();
        $this->assertFalse(isset($pages[0]['pages'][2]));
        $this->assertFalse(file_exists(Helper::getStorage() . "/pages/products_en-US.json"));
        $this->assertFalse(is_dir(Helper::getStorage() . "/pages/home page/products/"));
    }

    public function tearDown()
    {
        Helper::deleteStorage();
    }
}
