<?php

namespace Fluid\Tests\Files;

use Fluid, PHPUnit_Framework_TestCase, Fluid\Tests\Helper, Fluid\File\File;

class GetFilesTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Helper::createDevelop();
    }

    public function tearDown()
    {
        Helper::destroy();
    }

    public function testGetFiles()
    {
        // Test get files
        $request = array(
            "method" => "GET",
            "url" => "files",
            "data" => array()
        );

        ob_start();
        new Fluid\Requests\WebSocket($request['url'], $request['method'], $request['data'], 'develop', Helper::getUser());
        $retval = ob_get_contents();
        ob_end_clean();

        $retval = json_decode($retval, true);

        $this->assertEquals('l6K6DMQg', $retval[0]['id']);
        $this->assertEquals(1680, $retval[2]['width']);
        $this->assertEquals('image/jpeg', $retval[3]['type']);
    }

    public function testGetFile()
    {
        // Test get file
        $request = array(
            "method" => "GET",
            "url" => "files",
            "data" => array()
        );

        ob_start();
        new Fluid\Requests\WebSocket($request['url'], $request['method'], $request['data'], 'develop', Helper::getUser());
        $retval = ob_get_contents();
        ob_end_clean();

        $retval = json_decode($retval, true);

        $id = $retval[1]['id'];

        $file = File::get($id)->getInfo();

        $this->assertEquals('drvXRrtN', $file['id']);
        $this->assertEquals(2000, $file['width']);
        $this->assertEquals('image/jpeg', $file['type']);
    }
}
