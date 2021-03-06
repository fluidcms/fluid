<?php
namespace Fluid\Tests\Files;

use Fluid;
use PHPUnit_Framework_TestCase;
use Fluid\Tests\Helper;

class FilePreviewTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Helper::createDevelop();
    }

    public function tearDown()
    {
        Helper::destroy();
    }

    public function testGetComponent()
    {
        // Test get components
        $request = array(
            "method" => "GET",
            "url" => "file/preview/l6K6DMQg",
            "data" => array()
        );

        ob_start();
        new Fluid\Requests\WebSocket($request['url'], $request['method'], $request['data'], 'develop', Helper::getUser());
        $retval = ob_get_contents();
        ob_end_clean();

        $retval = json_decode($retval, true);

        $this->assertGreaterThan(5, strlen($retval['image']));
    }
}
