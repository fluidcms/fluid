<?php

namespace Fluid\Tests\Map;

use Fluid;
use PHPUnit_Framework_TestCase;
use Fluid\Tests\Helper;

class EditTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Helper::createDevelop();
    }

    public function tearDown()
    {
        Helper::destroy();
    }

    public function testEditMap()
    {
        $request = array(
            "method" => "PUT",
            "url" => "map",
            "data" => array(
                "id" => "home page/products",
                "page" => "our products",
                "languages" => array('en-US', 'de-DE'),
                "layout" => "default",
                "url" => "/our_products/"
            )
        );

        ob_start();
        new Fluid\Requests\WebSocket($request['url'], $request['method'], $request['data'], 'develop', Helper::getUser());
        $retval = ob_get_contents();
        ob_end_clean();

        $map = json_decode($retval, true);

        $this->assertFileNotExists(Helper::getStorage() . "/pages/home page/products_en-US.json");
        $this->assertFileNotExists(Helper::getStorage() . "/pages/home page/products_de-DE.json");
        $this->assertFileExists(Helper::getStorage() . "/pages/home page/our products_en-US.json");
        $this->assertFileExists(Helper::getStorage() . "/pages/home page/our products_de-DE.json");
        $this->assertFileExists(Helper::getStorage() . "/pages/home page/our products/awesome_en-US.json");
        $this->assertEquals('our products', $map[0]['pages'][2]['page']);
        $this->assertEquals('home page/our products/awesome', $map[0]['pages'][2]['pages'][0]['id']);
    }
}
