<?php

namespace Fluid\Tests\Page;

use Fluid, PHPUnit_Framework_TestCase, Fluid\Tests\Helper;

class GetPageTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Helper::createDevelop();
    }

    public function tearDown()
    {
        Helper::destroy();
    }

    public function testGetPage()
    {
        // Test global variables
        $request = array(
            "method" => "GET",
            "url" => "page/en-US/global",
            "data" => array()
        );

        ob_start();
        new Fluid\Requests\WebSocket($request['url'], $request['method'], $request['data'], 'develop', Helper::getUser());
        $retval = ob_get_contents();
        ob_end_clean();

        $retval = json_decode($retval, true);

        //$this->assertEquals('', $retval['data']['Site']['New Var']); TODO: this will not work as of now, see comment in Page::getRawData method
        $this->assertEquals('My Website', $retval['data']['Site']['Name']);
        $this->assertEquals('content', $retval['layoutDefinition']['Site']['Description']['type']);

        // Test home page
        $request = array(
            "method" => "GET",
            "url" => "page/en-US/home page",
            "data" => array()
        );

        ob_start();
        new Fluid\Requests\WebSocket($request['url'], $request['method'], $request['data'], 'develop', Helper::getUser());
        $retval = ob_get_contents();
        ob_end_clean();

        $retval = json_decode($retval, true);

        $this->assertEquals('Welcome', $retval['data']['Content']['Title']);
        $this->assertEquals('image', $retval['layoutDefinition']['Header']['Logo']['type']);
    }
}
