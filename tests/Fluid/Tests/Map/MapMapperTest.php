<?php
namespace Fluid\Tests\Map;

use Fluid\Map\MapMapper;
use Fluid\Tests\Helper;
use PHPUnit_Framework_TestCase;
use Fluid\Fluid;
use Fluid\Config;
use Fluid\Storage;
use Fluid\XmlMappingLoader;

class MapMapperTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Helper::createMaster();
    }

    public function tearDown()
    {
        Helper::destroy();
    }

    public function testConstruct()
    {
        $fluid = new Fluid;
        $mapMapper = new MapMapper(new Storage($fluid->getConfig()), new XmlMappingLoader($fluid), $fluid->getEvent());
        $this->assertInstanceOf('Fluid\StorageInterface', $mapMapper->getStorage());
        $this->assertInstanceOf('Fluid\XmlMappingLoaderInterface', $mapMapper->getXmlMappingLoader());
    }

    public function testMap()
    {
        $fluid = new Fluid;
        $fluid->getConfig()->setStorage(Helper::getStorage());
        $fluid->getConfig()->setMapping(__DIR__ . "/../_files/mapping");
        $mapMapper = new MapMapper(new Storage($fluid->getConfig()), new XmlMappingLoader($fluid), $fluid->getEvent());
        $pages = $mapMapper->map()->getPages();

        $this->assertArrayHasKey('home-page', $pages);
        $this->assertArrayHasKey('contact', $pages);
        $this->assertArrayHasKey('services', $pages);
        $this->assertArrayHasKey('products', $pages);
        $this->assertCount(4, $pages);

        /** @var \Fluid\Page\PageEntity $page */
        // home-page
        $page = $pages['home-page'];

        $this->assertEquals('home-page', $page->getName());
        $this->assertCount(0, $page->getPages());

        $pageConfig = $page->getConfig();

        $this->assertEquals('home', $pageConfig->getTemplate());
        $this->assertFalse($pageConfig->getAllowChilds());
        $this->assertFalse($pageConfig->allowChilds());
        $this->assertEquals('/', $pageConfig->getUrl());
        $this->assertNull($pageConfig->getChildTemplates());
        $this->assertInternalType('array', $pageConfig->getLanguages());
        $this->assertEquals('en-US', $pageConfig->getLanguages()[0]);
        $this->assertEquals('de-DE', $pageConfig->getLanguages()[1]);

        // contact
        $page = $pages['contact'];

        $this->assertEquals('contact', $page->getName());
        $this->assertCount(1, $page->getPages());

        $pageConfig = $page->getConfig();

        $this->assertEquals('contact', $pageConfig->getTemplate());
        $this->assertTrue($pageConfig->getAllowChilds());
        $this->assertTrue($pageConfig->allowChilds());
        $this->assertEquals('/contact', $pageConfig->getUrl());
        $this->assertInternalType('array', $pageConfig->getChildTemplates());
        $this->assertCount(2, $pageConfig->getChildTemplates());
        $this->assertEquals('contact', $pageConfig->getChildTemplates()[0]);
        $this->assertEquals('contact-form', $pageConfig->getChildTemplates()[1]);
        $this->assertInternalType('array', $pageConfig->getLanguages());
        $this->assertCount(1, $pageConfig->getLanguages());
        $this->assertEquals('en-US', $pageConfig->getLanguages()[0]);

        // contact/form
        $page = $pages['contact']['pages']['form'];

        $this->assertEquals('form', $page->getName());
        $this->assertCount(0, $page->getPages());

        $pageConfig = $page->getConfig();

        $this->assertEquals('contact-form', $pageConfig->getTemplate());
        $this->assertFalse($pageConfig->getAllowChilds());
        $this->assertFalse($pageConfig->allowChilds());
        $this->assertEquals('/contact/form', $pageConfig->getUrl());
        $this->assertNull($pageConfig->getChildTemplates());
        $this->assertInternalType('array', $pageConfig->getLanguages());
        $this->assertCount(0, $pageConfig->getLanguages());

        // services
        $page = $pages['services'];

        $this->assertEquals('services', $page->getName());
        $this->assertCount(0, $page->getPages());

        $pageConfig = $page->getConfig();

        $this->assertEquals('default', $pageConfig->getTemplate());
        $this->assertTrue($pageConfig->getAllowChilds());
        $this->assertTrue($pageConfig->allowChilds());
        $this->assertEquals('/services/', $pageConfig->getUrl());
        $this->assertNull($pageConfig->getChildTemplates());
        $this->assertInternalType('array', $pageConfig->getLanguages());
        $this->assertCount(1, $pageConfig->getLanguages());
        $this->assertEquals('en-US', $pageConfig->getLanguages()[0]);

        // products
        $page = $pages['products'];

        $this->assertEquals('products', $page->getName());
        $this->assertCount(1, $page->getPages());

        $pageConfig = $page->getConfig();

        $this->assertEquals('default', $pageConfig->getTemplate());
        $this->assertTrue($pageConfig->getAllowChilds());
        $this->assertTrue($pageConfig->allowChilds());
        $this->assertEquals('/products/', $pageConfig->getUrl());
        $this->assertNull($pageConfig->getChildTemplates());
        $this->assertInternalType('array', $pageConfig->getLanguages());
        $this->assertCount(1, $pageConfig->getLanguages());
        $this->assertEquals('en-US', $pageConfig->getLanguages()[0]);

        // products/awesome
        $page = $pages['products']['pages']['awesome'];

        $this->assertEquals('awesome', $page->getName());
        $this->assertCount(0, $page->getPages());

        $pageConfig = $page->getConfig();

        $this->assertEquals('default', $pageConfig->getTemplate());
        $this->assertTrue($pageConfig->getAllowChilds());
        $this->assertTrue($pageConfig->allowChilds());
        $this->assertEquals('/products/awesome/', $pageConfig->getUrl());
        $this->assertNull($pageConfig->getChildTemplates());
        $this->assertInternalType('array', $pageConfig->getLanguages());
        $this->assertCount(1, $pageConfig->getLanguages());
        $this->assertEquals('en-US', $pageConfig->getLanguages()[0]);
    }
}