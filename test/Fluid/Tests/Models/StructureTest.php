<?php

namespace Fluid\Tests\Models;

use Fluid\Fluid, Fluid\Models\Structure, PHPUnit_Framework_TestCase;

class StructureTest extends PHPUnit_Framework_TestCase {
	public function setUp() {
		Fluid::setConfig('storage', __DIR__.'/../Fixtures/storage/');
	}
	
	public function testInit() {
		$structure = new Structure();
				
		$this->assertInternalType('array', $structure->pages);
		
		return $structure;
    }
    
    /**
     * @depends testInit
     */
	public function testPagesIntegrity(Structure $structure) {
		foreach($structure->pages as $page) {
			$this->assertObjectHasAttribute('page', $page);
		}
    }
}
