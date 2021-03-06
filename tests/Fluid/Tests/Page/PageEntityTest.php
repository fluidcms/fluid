<?php
namespace Fluid\Tests\Page;

use Fluid\Fluid;
use Fluid\Storage;
use Fluid\XmlMappingLoader;
use Fluid\Page\PageEntity;
use Fluid\Page\PageMapper;

class PageEntityTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $fluid = new Fluid;
        $page = new PageEntity(
            $storage = new Storage($fluid->getConfig()),
            $xmlMappingLoader = new XmlMappingLoader($fluid->getConfig()),
            new PageMapper($storage, $xmlMappingLoader),
            $fluid->getLanguage()
        );
        $this->assertEquals(null, $page->getName());
        $this->assertInstanceOf('Fluid\Page\PageCollection', $page->getPages());
        $this->assertInstanceOf('Fluid\Variable\VariableCollection', $page->getVariables());
    }
}
