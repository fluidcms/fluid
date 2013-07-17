<?php

namespace Fluid\Tests\History;

use Fluid, PHPUnit_Framework_TestCase, Fluid\Tests\Helper;

class LanguageTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Helper::copyStorage();
    }

    public function testGetLanguage()
    {
        $request = array(
            "method" => "GET",
            "url" => "language",
            "data" => array()
        );

        ob_start();
        new Fluid\WebSockets\Requests($request['url'], $request['method'], $request['data'], 'develop', Helper::getUser());
        $retval = ob_get_contents();
        ob_end_clean();

        $language = json_decode($retval, true);

        $this->assertEquals('en-US', $language[0]['language']);
        $this->assertEquals('de-DE', $language[1]['language']);
    }

    public function tearDown()
    {
        Helper::deleteStorage();
    }
}
