<?php

namespace Fluid\Tests\Page;

use Fluid, PHPUnit_Framework_TestCase, Fluid\Tests\Helper;

class GetComponentTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Helper::copyStorage();
    }

    public function testGetComponent()
    {
        // Test get components
        $request = array(
            "method" => "GET",
            "url" => "component",
            "data" => array()
        );

        ob_start();
        new Fluid\WebSockets\Requests($request['url'], $request['method'], $request['data'], 'develop', Helper::getUser());
        $retval = ob_get_contents();
        ob_end_clean();

        $retval = json_decode($retval, true);

        $this->assertEquals('Accordion', $retval[0]['name']);
        $this->assertEquals('table', $retval[1]['component']);
        $this->assertGreaterThan(5, strlen($retval[0]['icon']));
    }

    public function tearDown()
    {
        Helper::deleteStorage();
    }
}
