<?php

namespace Fluid\Tests\History;

use Fluid;
use PHPUnit_Framework_TestCase;
use Fluid\Tests\Helper;

class GetTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Helper::createDevelop();
    }

    public function tearDown()
    {
        Helper::destroy();
    }

    public function testGetHistory()
    {
        $request = array(
            "method" => "GET",
            "url" => "history",
            "data" => array()
        );

        // Add some history
        file_put_contents(Helper::getStorage() . "/tmp", "test");
        Fluid\History\History::add('test123', 'PHPUnit', 'phpunit@localhost');

        unlink(Helper::getStorage() . "/tmp");
        Fluid\History\History::add('test456', 'PHPUnit', 'phpunit@localhost');

        file_put_contents(Helper::getStorage() . "/tmp", "test");
        Fluid\History\History::add('test789', 'PHPUnit', 'phpunit@localhost');

        ob_start();
        new Fluid\Requests\WebSocket($request['url'], $request['method'], $request['data'], 'develop', Helper::getUser());
        $retval = ob_get_contents();
        ob_end_clean();

        $history = json_decode($retval, true);

        $this->assertEquals('test123', $history[0]['action']);
        $this->assertEquals('test789', $history[2]['action']);
    }
}
