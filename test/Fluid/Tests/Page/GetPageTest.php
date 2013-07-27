<?php

namespace Fluid\Tests\Page;

use Fluid, PHPUnit_Framework_TestCase, Fluid\Tests\Helper;

class GetPageTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Helper::copyStorage();
    }

    public function testGetPage()
    {
        $request = array(
            "method" => "GET",
            "url" => "page/en-US/home page",
            "data" => array()
        );

        ob_start();
        new Fluid\WebSockets\Requests($request['url'], $request['method'], $request['data'], 'develop', Helper::getUser());
        $retval = ob_get_contents();
        ob_end_clean();

        $retval = json_decode($retval, true);

        $this->assertEquals('Welcome', $retval['data']['Content']['Title']);
        $this->assertEquals('image', $retval['layoutDefinition']['Header']['Logo']['type']);
    }

    public function tearDown()
    {
        Helper::deleteStorage();
    }
}
