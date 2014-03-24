<?php
namespace Fluid\Tests;

use PHPUnit_Framework_TestCase;
use Fluid\Fluid;
use Fluid\Mapping;
use SimpleXMLElement;
use Fluid\XmlMappingLoader;

class MappingTest extends PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $mapping = new Mapping(new SimpleXMLElement('<xml/>'));
        $this->assertInstanceOf('SimpleXMLElement', $mapping->getXmlElement());
    }

    public function testSetters()
    {
        $mapping = new Mapping();
        $mapping->setXmlElement(new SimpleXMLElement('<xml/>'));
        $this->assertInstanceOf('SimpleXMLElement', $mapping->getXmlElement());
    }

    public function testGetConfig()
    {
        $fluid = new Fluid;
        $fluid->getConfig()->setMapping(__DIR__ . '/_files/mapping');
        $mapping = (new XmlMappingLoader($fluid))->load('config.xml');
        $config = $mapping->getConfig();

        $this->assertArrayHasKey('name1', $config);
        $this->assertArrayHasKey('name2', $config);

        $this->assertEquals('value1', $config['name1']);
        $this->assertEquals('value2', $config['name2']);
    }

    public function testGetContent()
    {
        $fluid = new Fluid;
        $fluid->getConfig()->setMapping(__DIR__ . '/_files/mapping');
        $mapping = (new XmlMappingLoader($fluid))->load('content.xml');
        $content = $mapping->getContent();

        $this->assertCount(3, $content);
        $this->assertArrayHasKey(0, $content);
        $this->assertArrayHasKey('name', $content[0]);
        $this->assertArrayHasKey('attributes', $content[0]);
        $this->assertEquals('page', $content[0]['name']);
        $this->assertArrayHasKey('id', $content[0]['attributes']);
        $this->assertEquals('home-page', $content[0]['attributes']['id']);

        $this->assertArrayHasKey(2, $content[0]);
        $this->assertArrayHasKey('name', $content[0][0]);
        $this->assertArrayHasKey('attributes', $content[0][0]);
        $this->assertEquals('setting', $content[0][0]['name']);
        $this->assertArrayHasKey('name', $content[0][0]['attributes']);
        $this->assertEquals('template', $content[0][0]['attributes']['name']);
    }
}