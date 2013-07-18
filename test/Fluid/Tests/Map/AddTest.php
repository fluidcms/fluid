<?php

namespace Fluid\Tests\Map;

use Fluid, PHPUnit_Framework_TestCase, Fluid\Tests\Helper;

class AddTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Helper::copyStorage();
    }

    public function testAddMap()
    {
        $request = array(
            "method" => "POST",
            "url" => "map",
            "data" => array(
                "index" => 0,
                "languages" => array('en-US', 'de-DE'),
                "layout" => "default",
                "page" => "My Blog",
                "url" => "/blog/",
                "parent" => ""
            )
        );

        ob_start();
        new Fluid\WebSockets\Requests($request['url'], $request['method'], $request['data'], 'develop', Helper::getUser());
        $retval = ob_get_contents();
        ob_end_clean();

        $map = json_decode($retval, true);

        $this->assertFileExists(Helper::getStorage() . "/pages/My Blog_en-US.json");
        $this->assertFileExists(Helper::getStorage() . "/pages/My Blog_de-DE.json");
        $this->assertEquals('My Blog', $map[0]['page']);

        // Add map to sub page
        $request = array(
            "method" => "POST",
            "url" => "map",
            "data" => array(
                "index" => 0,
                "languages" => array('de-DE'),
                "layout" => "home",
                "page" => "New Page",
                "url" => "",
                "parent" => "home page/contact"
            )
        );

        ob_start();
        new Fluid\WebSockets\Requests($request['url'], $request['method'], $request['data'], 'develop', Helper::getUser());
        $retval = ob_get_contents();
        ob_end_clean();

        $map = json_decode($retval, true);

        $this->assertFileNotExists(Helper::getStorage() . "/pages/home page/contact/New Page_en-US.json");
        $this->assertFileExists(Helper::getStorage() . "/pages/home page/contact/New Page_de-DE.json");
        $this->assertEquals('New Page', $map[1]['pages'][0]['pages'][0]['page']);
    }

    public function tearDown()
    {
        Helper::deleteStorage();
    }
}
