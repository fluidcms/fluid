<?php
namespace Fluid\Tests\Page;

use Fluid;
use Fluid\Page\PageEntity;

class PageEntityTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $pageEntity = new PageEntity();
        $this->assertEquals(null, $pageEntity->getId());
        $this->assertEquals(null, $pageEntity->getLayout());
        $this->assertEquals(null, $pageEntity->getName());
        $this->assertEquals(null, $pageEntity->getUrl());
        $this->assertInternalType('array', $pageEntity->getLanguages());
        $this->assertInstanceOf('Fluid\Page\PageRepository', $pageEntity->getPages());
        $this->assertInstanceOf('Fluid\Page\VariableRepository', $pageEntity->getVariables());

        $pageEntity = new PageEntity([
            'id' => 'my_page',
            'layout' => 'my_layout',
        ]);

    }
}
