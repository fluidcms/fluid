<?php

namespace Fluid\Tests\Files;

use Fluid, PHPUnit_Framework_TestCase, Fluid\Tests\Helper;

class GetFilesTest extends PHPUnit_Framework_TestCase
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
            "url" => "files",
            "data" => array()
        );

        ob_start();
        new Fluid\WebSockets\Requests($request['url'], $request['method'], $request['data'], 'develop', Helper::getUser());
        $retval = ob_get_contents();
        ob_end_clean();

        $retval = json_decode($retval, true);

        $this->assertEquals('7tIrGTOd', $retval[0]['id']);
        $this->assertEquals(2000, $retval[2]['width']);
        $this->assertEquals('image/jpeg', $retval[3]['type']);
    }

    public function tearDown()
    {
        Helper::deleteStorage();
    }
}
