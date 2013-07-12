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
        Fluid\History\History::add('test', 'PHPUnit', 'phpunit@localhost');

        unlink(Helper::getStorage() . "/tmp");
        Fluid\History\History::add('test', 'PHPUnit', 'phpunit@localhost');

        file_put_contents(Helper::getStorage() . "/tmp", "test");
        Fluid\History\History::add('test', 'PHPUnit', 'phpunit@localhost');

        $request = array(
            "method" => "GET",
            "url" => "history",
            "data" => array()
        );

        ob_start();
        new Fluid\WebSockets\Requests($request['url'], $request['method'], $request['data'], 'develop', Helper::getUser());
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
        new Fluid\WebSockets\Requests($request['url'], $request['method'], $request['data'], 'develop', Helper::getUser());
        $retval = ob_get_contents();
        ob_end_clean();

        $this->assertTrue(true);

        // TODO: some real tests maybe?
    }

    public function tearDown()
    {
        Helper::deleteStorage();
    }
}
