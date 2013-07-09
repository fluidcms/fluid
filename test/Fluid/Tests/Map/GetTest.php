<?php

namespace Fluid\Tests\Map;

use Fluid, PHPUnit_Framework_TestCase, Fluid\Tests\Helper;

class GetTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Helper::copyStorage();
    }

    public function testGet()
    {
        // Test sub page to root
        $request = array(
            "method" => "GET",
            "url" => "map",
            "data" => array()
        );

        ob_start();
        new Fluid\WebSockets\Requests($request['url'], $request['method'], $request['data'], 'develop', Helper::getUser());
        $retval = ob_get_contents();
        ob_end_clean();

        $map = json_decode($retval, true);

        $this->assertEquals('contact', $map[0]['pages'][0]['page']);
    }

    public function tearDown()
    {
        Helper::deleteStorage();
    }
}
