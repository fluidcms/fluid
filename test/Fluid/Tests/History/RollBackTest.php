<?php

namespace Fluid\Tests\History;

use Fluid, PHPUnit_Framework_TestCase, Fluid\Tests\Helper;

class RollBackTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Helper::copyStorage();
    }

    public function testRollBackHistory()
    {
        // Add some history
        file_put_contents(Helper::getStorage() . "/tmp", "test");
        Fluid\History\History::add('test123', 'PHPUnit', 'phpunit@localhost');

        unlink(Helper::getStorage() . "/tmp");
        Fluid\History\History::add('test456', 'PHPUnit', 'phpunit@localhost');

        file_put_contents(Helper::getStorage() . "/tmp", "test");
        Fluid\History\History::add('test789', 'PHPUnit', 'phpunit@localhost');

        $request = array(
            "method" => "GET",
            "url" => "history",
            "data" => array()
        );

        ob_start();
        new Fluid\Requests\WebSocket($request['url'], $request['method'], $request['data'], 'develop', Helper::getUser());
        $retval = ob_get_contents();
        ob_end_clean();

        $history = json_decode($retval, true);

        // Roll back
        $request = array(
            "method" => "PUT",
            "url" => "history/".$history[1]['id'],
            "data" => array()
        );

        ob_start();
        new Fluid\Requests\WebSocket($request['url'], $request['method'], $request['data'], 'develop', Helper::getUser());
        $retval = ob_get_contents();
        ob_end_clean();

        $history = json_decode($retval, true);

        $this->assertEquals('test123', $history[0]['action']);
        $this->assertFalse($history[0]['ghost']);
        $this->assertEquals('test789', $history[2]['action']);
        $this->assertTrue($history[2]['ghost']);

        // Roll forward
        $request = array(
            "method" => "PUT",
            "url" => "history/".$history[2]['id'],
            "data" => array()
        );

        ob_start();
        new Fluid\Requests\WebSocket($request['url'], $request['method'], $request['data'], 'develop', Helper::getUser());
        $retval = ob_get_contents();
        ob_end_clean();

        $history = json_decode($retval, true);

        $this->assertEquals('test123', $history[0]['action']);
        $this->assertFalse($history[0]['ghost']);
        $this->assertEquals('test789', $history[2]['action']);
        $this->assertFalse($history[2]['ghost']);

        // Overwrite forward changes
        $request = array(
            "method" => "PUT",
            "url" => "history/".$history[1]['id'],
            "data" => array()
        );

        ob_start();
        new Fluid\Requests\WebSocket($request['url'], $request['method'], $request['data'], 'develop', Helper::getUser());
        ob_end_clean();

        file_put_contents(Helper::getStorage() . "/tmp", "test");
        Fluid\History\History::add('test999', 'PHPUnit', 'phpunit@localhost');

        $request = array(
            "method" => "GET",
            "url" => "history",
            "data" => array()
        );

        ob_start();
        new Fluid\Requests\WebSocket($request['url'], $request['method'], $request['data'], 'develop', Helper::getUser());
        $retval = ob_get_contents();
        ob_end_clean();

        $history = json_decode($retval, true);

        $this->assertEquals('test123', $history[0]['action']);
        $this->assertFalse($history[0]['ghost']);
        $this->assertEquals('test999', $history[2]['action']);
        $this->assertFalse($history[2]['ghost']);
    }

    public function tearDown()
    {
        Helper::deleteStorage();
    }
}
