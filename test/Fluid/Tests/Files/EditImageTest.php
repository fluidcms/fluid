<?php

namespace Fluid\Tests\Files;

use Fluid;
use PHPUnit_Framework_TestCase;
use Fluid\Tests\Helper;
use Fluid\Page\Page;
use Fluid\Map\Map;

class EditImageTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Helper::createDevelop();
    }

    public function tearDown()
    {
        Helper::destroy();
    }

    public function testEditImage()
    {
        $page = Page::get(null, 'en-US');

        $data = $page->getRawData();

        $data['Site']['Logo'] = '0i7ygv3r';

        $request = array(
            "method" => "PUT",
            "url" => "page/en-US/global",
            "data" => $data
        );

        ob_start();
        new Fluid\Requests\WebSocket($request['url'], $request['method'], $request['data'], 'develop', Helper::getUser());
        $retval = ob_get_contents();
        ob_end_clean();

        $retval = json_decode($retval, true);

        $this->assertEquals(180, $retval['data']['Site']['Logo']['width']);
        $this->assertEquals(360, $retval['data']['Site']['Logo']['Retina']['width']);
        $this->assertEquals(143, $retval['data']['Site']['Logo']['NoHeight']['height']);
        $this->assertEquals('/fluidcms/images/0i7ygv3r/Logo.png', $retval['data']['Site']['Logo']['Original']['src']);

        $dir = Fluid\Fluid::getBranchStorage() . "files";
        $this->assertFileExists(preg_replace('!^/fluidcms/images!', $dir, $retval['data']['Site']['Logo']['src']));
        $this->assertFileExists(preg_replace('!^/fluidcms/images!', $dir, $retval['data']['Site']['Logo']['Retina']['src']));
    }

    public function testEditImageInArray()
    {
        $page = Page::get(null, 'en-US');

        $data = $page->getRawData();

        $data['Site']['MyArray'][]['Image'] = '0i7ygv3r';

        $request = array(
            "method" => "PUT",
            "url" => "page/en-US/global",
            "data" => $data
        );

        ob_start();
        new Fluid\Requests\WebSocket($request['url'], $request['method'], $request['data'], 'develop', Helper::getUser());
        $retval = ob_get_contents();
        ob_end_clean();

        $retval = json_decode($retval, true);

        $this->assertEquals(300, $retval['data']['Site']['MyArray'][0]['Image']['width']);
        $this->assertEquals(86, $retval['data']['Site']['MyArray'][0]['Image']['height']);
        $this->assertEquals('/fluidcms/images/0i7ygv3r/Logo.png', $retval['data']['Site']['MyArray'][0]['Image']['src']);

        $dir = Fluid\Fluid::getBranchStorage() . "files";
        $this->assertFileExists(preg_replace('!^/fluidcms/images!', $dir, $retval['data']['Site']['MyArray'][0]['Image']['src']));
    }
}
