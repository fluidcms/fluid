<?php

namespace Fluid\Tests\Page;

use Fluid;
use PHPUnit_Framework_TestCase;
use Fluid\Tests\Helper;

class GetContentVariableTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Helper::createDevelop();
    }

    public function tearDown()
    {
        Helper::destroy();
    }

    public function testGetContentVariable()
    {
        // Test global variables
        $request = array(
            "method" => "GET",
            "url" => "page_variable/en-US/home page/Content/Content",
            "data" => array()
        );

        ob_start();
        new Fluid\Requests\WebSocket($request['url'], $request['method'], $request['data'], 'develop', Helper::getUser());
        $retval = ob_get_contents();
        ob_end_clean();

        $retval = json_decode($retval, true);

        $this->assertEquals("Hello World {PkPUah3bme2qvkTK} {H7inutVo}", $retval['source']);
        $this->assertEquals('Hello World', $retval['components']['H7inutVo']['data']['Name']);
    }
}
