<?php

namespace Fluid\Tests\Page;

use Fluid,
    PHPUnit_Framework_TestCase,
    Fluid\Tests\Helper,
    Fluid\Page\Page,
    Fluid\Map\Map;

class EditPageTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Helper::copyStorage();
    }

    public function testEditPage()
    {
        // Global data
        $page = Page::get(null, 'en-US');

        $data = $page->getRawData();

        $data['Site']['Name'] = 'My Awesome Website';

        $request = array(
            "method" => "PUT",
            "url" => "page/en-US/global",
            "data" => $data
        );

        ob_start();
        new Fluid\WebSockets\Requests($request['url'], $request['method'], $request['data'], 'develop', Helper::getUser());
        $retval = ob_get_contents();
        ob_end_clean();

        $retval = json_decode($retval, true);

        $this->assertEquals($data['Site']['Name'], $retval['data']['Site']['Name']);

        // Home page
        $map = new Map;
        $mapPage = $map->findPage('home page');
        $page = Page::get($mapPage, 'en-US');

        $data = $page->getRawData();

        $data['Content']['Content']['source'] = "Hello World, how are you today? {PkPUah3bme2qvkTK}";

        $request = array(
            "method" => "PUT",
            "url" => "page/en-US/home page",
            "data" => $data
        );

        ob_start();
        new Fluid\WebSockets\Requests($request['url'], $request['method'], $request['data'], 'develop', Helper::getUser());
        $retval = ob_get_contents();
        ob_end_clean();

        $retval = json_decode($retval, true);

        $this->assertEquals($data['Content']['Content']['source'], $retval['data']['Content']['Content']['source']);
    }

    public function testEditUniversal()
    {
        // Home page
        $map = new Map;
        $mapPage = $map->findPage('home page');
        $page = Page::get($mapPage, 'en-US');

        $data = $page->getRawData();

        $data['Bilingual']['Name'] = "This is a test";

        $request = array(
            "method" => "PUT",
            "url" => "page/en-US/home page",
            "data" => $data
        );

        ob_start();
        new Fluid\WebSockets\Requests($request['url'], $request['method'], $request['data'], 'develop', Helper::getUser());
        ob_end_clean();

        // Get page in other language
        $request = array(
            "method" => "GET",
            "url" => "page/de-DE/home page",
            "data" => array()
        );

        ob_start();
        new Fluid\WebSockets\Requests($request['url'], $request['method'], $request['data'], 'develop', Helper::getUser());
        $retval = ob_get_contents();
        ob_end_clean();

        $retval = json_decode($retval, true);

        $this->assertEquals($data['Bilingual']['Name'], $retval['data']['Bilingual']['Name']);
    }

    public function tearDown()
    {
        Helper::deleteStorage();
    }
}
