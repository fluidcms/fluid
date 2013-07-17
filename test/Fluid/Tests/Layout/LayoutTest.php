<?php

namespace Fluid\Tests\History;

use Fluid, PHPUnit_Framework_TestCase, Fluid\Tests\Helper;

class LayoutTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Helper::copyStorage();
        Fluid\Fluid::setConfig('templates', Helper::getFixtureDir() . '/templates');
        Fluid\Fluid::setConfig('layouts', 'layouts');
    }

    public function testGetLayouts()
    {
        $request = array(
            "method" => "GET",
            "url" => "layout",
            "data" => array()
        );

        ob_start();
        new Fluid\WebSockets\Requests($request['url'], $request['method'], $request['data'], 'develop', Helper::getUser());
        $retval = ob_get_contents();
        ob_end_clean();

        $retval = json_decode($retval, true);

        $this->assertEquals('default', $retval[0]['layout']);
        $this->assertEquals('home', $retval[1]['layout']);
    }

    public function tearDown()
    {
        Helper::deleteStorage();
    }
}
