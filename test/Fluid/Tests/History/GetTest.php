<?php

namespace Fluid\Tests\History;

use Fluid, PHPUnit_Framework_TestCase, Fluid\Tests\Helper;

class GetTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Helper::copyStorage();
    }

    public function testGetHistory()
    {
        $request = array(
            "method" => "GET",
            "url" => "history",
            "data" => array()
        );

        // Add items to history
        $delete = new Fluid\Tests\Map\SortTest;
        $delete->testSort();

        ob_start();
        new Fluid\WebSockets\Requests($request['url'], $request['method'], $request['data'], 'develop', Helper::getUser());
        $retval = ob_get_contents();
        ob_end_clean();

        $history = json_decode($retval, true);

        $this->assertEquals('map_sort', $history[0]['action']);
        $this->assertEquals('map_sort', $history[2]['action']);
    }

    public function tearDown()
    {
        Helper::deleteStorage();
    }
}
