<?php

namespace Fluid\Tests\History;

use Fluid;
use PHPUnit_Framework_TestCase;
use Fluid\Tests\Helper;

class LanguageTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Helper::createDevelop();
    }

    public function tearDown()
    {
        Helper::destroy();
    }

    public function testGetLanguage()
    {
        $request = array(
            "method" => "GET",
            "url" => "language",
            "data" => array()
        );

        ob_start();
        new Fluid\Requests\WebSocket($request['url'], $request['method'], $request['data'], 'develop', Helper::getUser());
        $retval = ob_get_contents();
        ob_end_clean();

        $language = json_decode($retval, true);

        $this->assertEquals('en-US', $language[0]['language']);
        $this->assertEquals('de-DE', $language[1]['language']);
    }
}
