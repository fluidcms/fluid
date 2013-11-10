<?php

namespace Fluid\Tests\Map;

use Fluid;
use PHPUnit_Framework_TestCase;
use Fluid\Tests\Helper;

class GetTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Helper::createDevelop();
    }

    public function tearDown()
    {
        Helper::destroy();
    }

    public function testGetMap()
    {
        $request = array(
            "method" => "GET",
            "url" => "map",
            "data" => array()
        );

        ob_start();
        new Fluid\Requests\WebSocket($request['url'], $request['method'], $request['data'], 'develop', Helper::getUser());
        $retval = ob_get_contents();
        ob_end_clean();

        $map = json_decode($retval, true);

        $this->assertEquals('contact', $map[0]['pages'][0]['page']);
    }
}
