<?php

namespace Fluid\Tests\Page;

use Fluid;
use PHPUnit_Framework_TestCase;
use Fluid\Tests\Helper;
use Fluid\Page\Page;
use Fluid\Map\Map;

class EditPageTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Helper::createDevelop();
    }

    public function tearDown()
    {
        Helper::destroy();
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
        new Fluid\Requests\WebSocket($request['url'], $request['method'], $request['data'], 'develop', Helper::getUser());
        $retval = ob_get_contents();
        ob_end_clean();

        $retval = json_decode($retval, true);

        $this->assertEquals($data['Site']['Name'], $retval['data']['Site']['Name']);

        // Home page
        $map = new Map;
        $mapPage = $map->findPage('home page');
        $page = Page::get($mapPage, 'en-US');

        $data = $page->getRawData();

        $data['Content']['Content']['source'] = "Hello World, how are you today? {PkPUah3bme2qvkTK} {H7inutVo}";

        $request = array(
            "method" => "PUT",
            "url" => "page/en-US/home page",
            "data" => $data
        );

        ob_start();
        new Fluid\Requests\WebSocket($request['url'], $request['method'], $request['data'], 'develop', Helper::getUser());
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
        new Fluid\Requests\WebSocket($request['url'], $request['method'], $request['data'], 'develop', Helper::getUser());
        ob_end_clean();

        // Get page in other language
        $request = array(
            "method" => "GET",
            "url" => "page/de-DE/home page",
            "data" => array()
        );

        ob_start();
        new Fluid\Requests\WebSocket($request['url'], $request['method'], $request['data'], 'develop', Helper::getUser());
        $retval = ob_get_contents();
        ob_end_clean();

        $retval = json_decode($retval, true);

        $this->assertEquals($data['Bilingual']['Name'], $retval['data']['Bilingual']['Name']);
    }

    public function testEditArray()
    {
        // Home page
        $map = new Map;
        $mapPage = $map->findPage('home page');
        $page = Page::get($mapPage, 'en-US');

        $data = $page->getRawData();

        $data['Content']['Sections'] = array(
            array(
                "Name" => "Test",
                "Image" => array(
                    "src" => "/fluidcms/images/y3gsv57j/My Logo.png",
                    "alt" => "",
                    "width" => "64",
                    "height" => "64"
                )
            )
        );

        $request = array(
            "method" => "PUT",
            "url" => "page/en-US/home page",
            "data" => $data
        );

        ob_start();
        new Fluid\Requests\WebSocket($request['url'], $request['method'], $request['data'], 'develop', Helper::getUser());
        $retval = ob_get_contents();
        ob_end_clean();

        $retval = json_decode($retval, true);

        $this->assertEquals($data['Content']['Sections'][0]['Name'], $retval['data']['Content']['Sections'][0]['Name']);
        $this->assertEquals(1, count($retval['data']['Content']['Sections']));
    }

    public function testEditComponentArray()
    {
        // Home page
        $map = new Map;
        $mapPage = $map->findPage('home page');
        $page = Page::get($mapPage, 'en-US');

        $data = $page->getRawData();

        $data['Content']['Content']['source'] = "Hello World {PkPUah3bme2qvkTK} {H7inutVo} {3o7367Wy}";
        $data['Content']['Content']['components']['3o7367Wy'] = array(
            "component" => "table2",
            "data" => array(
                "Rows" => array(
                    array(
                        "Description" => array(
                            "source" => "This is the item description",
                            "components" => array(),
                            "images" => array()
                        ),
                        "Quantity" => "2",
                        "Amount" => "4.00",
                        "Total" => "8.00"
                    ),
                    array(
                        "Description" => array(
                            "source" => "Item 2",
                            "components" => array(),
                            "images" => array()
                        ),
                        "Quantity" => "6",
                        "Amount" => "13.00",
                        "Total" => "78.00"
                    )
                )
            )
        );

        $request = array(
            "method" => "PUT",
            "url" => "page/en-US/home page",
            "data" => $data
        );

        ob_start();
        new Fluid\Requests\WebSocket($request['url'], $request['method'], $request['data'], 'develop', Helper::getUser());
        $retval = ob_get_contents();
        ob_end_clean();

        $retval = json_decode($retval, true);

        $this->assertEquals(
            $data['Content']['Content']['components']['3o7367Wy']['data']['Rows'][0]['Quantity'],
            $retval['data']['Content']['Content']['components']['3o7367Wy']['data']['Rows'][0]['Quantity']
        );
        $this->assertEquals(2, count($retval['data']['Content']['Content']['components']['3o7367Wy']['data']['Rows']));
    }

    public function testEditOption()
    {
        // Try sending null to see if it will save the first option
        $map = new Map;
        $mapPage = $map->findPage('home page');
        $page = Page::get($mapPage, 'en-US');

        $data = $page->getRawData();

        unset($data['Content']['Status']);

        $request = array(
            "method" => "PUT",
            "url" => "page/en-US/home page",
            "data" => $data
        );

        ob_start();
        new Fluid\Requests\WebSocket($request['url'], $request['method'], $request['data'], 'develop', Helper::getUser());
        $retval = ob_get_contents();
        ob_end_clean();

        $retval = json_decode($retval, true);

        $this->assertEquals('Open', $retval['data']['Content']['Status']);


        // Choose an option
        $map = new Map;
        $mapPage = $map->findPage('home page');
        $page = Page::get($mapPage, 'en-US');

        $data = $page->getRawData();

        $data['Content']['Status'] = '0';

        $request = array(
            "method" => "PUT",
            "url" => "page/en-US/home page",
            "data" => $data
        );

        ob_start();
        new Fluid\Requests\WebSocket($request['url'], $request['method'], $request['data'], 'develop', Helper::getUser());
        $retval = ob_get_contents();
        ob_end_clean();

        $retval = json_decode($retval, true);

        $this->assertEquals('0', $retval['data']['Content']['Status']);
    }

    public function testEditBool()
    {
        $map = new Map;
        $mapPage = $map->findPage('home page');
        $page = Page::get($mapPage, 'en-US');

        $data = $page->getRawData();

        $data['Content']['Available'] = '1';

        $request = array(
            "method" => "PUT",
            "url" => "page/en-US/home page",
            "data" => $data
        );

        ob_start();
        new Fluid\Requests\WebSocket($request['url'], $request['method'], $request['data'], 'develop', Helper::getUser());
        $retval = ob_get_contents();
        ob_end_clean();

        $retval = json_decode($retval, true);

        $this->assertTrue($retval['data']['Content']['Available']);
    }

    public function testEditTable()
    {
        // Home page
        $map = new Map;
        $mapPage = $map->findPage('home page');
        $page = Page::get($mapPage, 'en-US');

        $data = $page->getRawData();

        $data['Content']['Content']['source'] .= '{LAg2T3VE}';
        $data['Content']['Content']['components']['LAg2T3VE'] = array(
            'component' => 'table',
            'data' => array(
                'Table' => array(
                    'tbody' => array(
                        array('test1', 'test2'),
                        array('test3', 'test4')
                    ),
                    'thead' => array('thead test1', 'thead test2'),
                    'tfoot' => array('tfoot test1', 'tfoot test2')
                )
            )
        );

        $request = array(
            "method" => "PUT",
            "url" => "page/en-US/home page",
            "data" => $data
        );

        ob_start();
        new Fluid\Requests\WebSocket($request['url'], $request['method'], $request['data'], 'develop', Helper::getUser());
        $retval = ob_get_contents();
        ob_end_clean();

        $retval = json_decode($retval, true);

        $this->assertEquals(
            $data['Content']['Content']['components']['LAg2T3VE']['data']['Table']['thead'][0],
            $retval['data']['Content']['Content']['components']['LAg2T3VE']['data']['Table']['thead'][0]
        );
    }
}
