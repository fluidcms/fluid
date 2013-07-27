<?php

namespace Fluid\Tests\Token;

use Fluid, PHPUnit_Framework_TestCase, Fluid\Tests\Helper;

class GetTokenTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Helper::copyStorage();
    }

    public function testGetToken()
    {
        $request = array(
            "method" => "GET",
            "url" => "token",
            "data" => array()
        );

        ob_start();
        new Fluid\WebSockets\Requests($request['url'], $request['method'], $request['data'], 'develop', Helper::getUser());
        $retval = ob_get_contents();
        ob_end_clean();

        $retval = json_decode($retval, true);

        $this->assertEquals(64, strlen($retval['token']));
    }

    public function tearDown()
    {
        Helper::deleteStorage();
    }
}
