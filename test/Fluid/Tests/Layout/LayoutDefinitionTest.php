<?php

namespace Fluid\Tests\Layout;

use Fluid;
use PHPUnit_Framework_TestCase;
use Fluid\Tests\Helper;

class LayoutDefinitionTest extends PHPUnit_Framework_TestCase
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
            "url" => "layout/home",
            "data" => array()
        );

        ob_start();
        new Fluid\Requests\WebSocket($request['url'], $request['method'], $request['data'], 'develop', Helper::getUser());
        $retval = ob_get_contents();
        ob_end_clean();

        $retval = json_decode($retval, true);

        $this->assertEquals('string', $retval['Header']['Title']['type']);
        $this->assertEquals('64', $retval['Content']['Sections']['variables']['Image']['width']);
        $this->assertEquals('components', $retval['Sidebar']['Sidebar']['type']);

        $request = array(
            "method" => "GET",
            "url" => "layout/global",
            "data" => array()
        );

        ob_start();
        new Fluid\Requests\WebSocket($request['url'], $request['method'], $request['data'], 'develop', Helper::getUser());
        $retval = ob_get_contents();
        ob_end_clean();

        $retval = json_decode($retval, true);

        $this->assertEquals('string', $retval['Site']['Name']['type']);
        $this->assertEquals('png', $retval['Site']['Logo']['format']);
    }
}
