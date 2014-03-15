<?php
namespace Fluid\Tests;

use Fluid\Config;
use Fluid\Tests\Mock\TemplateEngineMock;
use PHPUnit_Framework_TestCase;
use Fluid\Fluid;
use Fluid\Tests\Mock\ConfigMock;

class FluidTest extends PHPUnit_Framework_TestCase
{
    public function testSettersAndGetters()
    {
        $fluid = new Fluid;
        $this->assertInstanceOf('\Fluid\Config', $fluid->getConfig());
        $this->assertInstanceOf('\Fluid\TemplateEngine', $fluid->getTemplateEngine());
        $this->assertInstanceOf('\Fluid\Map\Map', $fluid->getMap());

        $fluid->setConfig(new ConfigMock);
        $this->assertInstanceOf('\Fluid\Tests\Mock\ConfigMock', $fluid->getConfig());

        $fluid->setTemplateEngine(new TemplateEngineMock);
        $this->assertInstanceOf('\Fluid\Tests\Mock\TemplateEngineMock', $fluid->getTemplateEngine());
    }

    /**
     * @expectedException \Fluid\Exception\InvalidDebugModeException
     */
    public function testDebugMode()
    {
        $fluid = new Fluid;

        $this->assertEquals($fluid::DEBUG_OFF, $fluid->getDebugMode());
        $fluid->debug($fluid::DEBUG_LOG);
        $this->assertEquals($fluid::DEBUG_LOG, $fluid->getDebugMode());
        $fluid->debug('something unrelated');
    }
}