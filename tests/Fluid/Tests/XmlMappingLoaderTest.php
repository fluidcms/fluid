<?php
namespace Fluid\Tests;

use Fluid\Tests\Mock\ConfigMock;
use Fluid\Tests\Mock\FluidMock;
use PHPUnit_Framework_TestCase;
use Fluid\Fluid;
use Fluid\XmlMappingLoader;

class XmlMappingLoaderTest extends PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $fluid = new Fluid;
        $xmlMappingLoader = new XmlMappingLoader($fluid->getConfig());
        $this->assertInstanceOf('Fluid\Config', $xmlMappingLoader->getConfig());
    }

    public function testSetters()
    {
        $xmlMappingLoader = new XmlMappingLoader(new ConfigMock);
        $this->assertInstanceOf('Fluid\Tests\Mock\ConfigMock', $xmlMappingLoader->getConfig());
    }

    public function testLoad()
    {
        $fluid = new Fluid;
        $fluid->getConfig()->setMapping(__DIR__ . '/_files/mapping');
        $this->assertInstanceOf('Fluid\Mapping', (new XmlMappingLoader($fluid->getConfig()))->load('map.xml'));
    }
}