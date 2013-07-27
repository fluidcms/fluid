<?php

/*[2,"0.9jwuqtnxc7ue4s4i","{\"branch\":\"develop\",\"user_id\":\"51f2b1d8adf26\",\"user_name\":\"Gabriel Bull\",\"user_email\":\"gavroche.bull@gmail.com\"}",
{"method":"PUT","url":"page/Home Page","data":{"Header":{"Title":"Page Title","Description":"Page Description"},"Content":{"Title":"Welcome","Content":{"source":"\n        Hello World {PkPUah3bme2qvkTK}\n    ","components":{},"images":{"PkPUah3bme2qvkTK":{"src":"/fluidcms/images/y3gsv57j/My Logo.png","alt":"","width":"","height":""}}},"Status":"Closed","Sections":[{"Name":"Test","Image":{"src":"/fluidcms/images/y3gsv57j/My Logo.png","alt":"","width":"64","height":"64"}},{"Name":"Test 2","Image":{"src":"/fluidcms/images/y3gsv57j/My Logo.png","alt":"","width":"64","height":"64"}}]},"Sidebar":{"Sidebar":{"source":"{qvbZhieA7r8efNDs}{RMhQkX8KBsxoAh4G}","components":{"qvbZhieA7r8efNDs":{"component":"table","data":{"Table":{"header":[["Column 1","Column 2","Column 3"]],"body":[["Val1","Val2","Val3"],["Val4","Val5","Val6"]],"footer":[["","","Total: 54"]]}}}}}}}}]
*/

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

    public function tearDown()
    {
        Helper::deleteStorage();
    }
}
