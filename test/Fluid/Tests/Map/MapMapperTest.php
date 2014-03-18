<?php
namespace Fluid\Tests\Map;

use Fluid\Map\MapMapper;
use Fluid\Tests\Helper;
use PHPUnit_Framework_TestCase;
use Fluid\Fluid;
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
        $mapMapper = new MapMapper(new Storage($fluid), new XmlMappingLoader($fluid));
        $this->assertInstanceOf('Fluid\StorageInterface', $mapMapper->getStorage());
        $this->assertInstanceOf('Fluid\XmlMappingLoaderInterface', $mapMapper->getXmlMappingLoader());
    }

    public function testMap()
    {
        $fluid = new Fluid;
        $fluid->getConfig()->setStorage(Helper::getStorage());
        $fluid->getConfig()->setMapping(__DIR__ . "/../_files/mapping");
        $mapMapper = new MapMapper(new Storage($fluid), new XmlMappingLoader($fluid));
        $map = $mapMapper->map();

        echo '';
    }
}