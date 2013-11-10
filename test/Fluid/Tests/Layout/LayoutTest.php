<?php

namespace Fluid\Tests\History;

use Fluid;
use PHPUnit_Framework_TestCase;
use Fluid\Tests\Helper;

class LayoutTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Helper::createDevelop();
    }

    public function tearDown()
    {
        Helper::destroy();
    }

    public function testGetLayouts()
    {
        $request = array(
            "method" => "GET",
            "url" => "layout",
            "data" => array()
        );

        ob_start();
        new Fluid\Requests\WebSocket($request['url'], $request['method'], $request['data'], 'develop', Helper::getUser());
        $retval = ob_get_contents();
        ob_end_clean();

        $retval = json_decode($retval, true);

        $this->assertEquals('default', $retval[0]['layout']);
        $this->assertEquals('home', $retval[1]['layout']);
    }
}
