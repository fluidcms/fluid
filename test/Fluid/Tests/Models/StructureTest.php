<?php

namespace Fluid\Tests\Models;

use Fluid\Fluid, Fluid\Models\Structure, PHPUnit_Framework_TestCase;

class StructureTest extends PHPUnit_Framework_TestCase {
	public function setUp() {
		Fluid::setLanguage('de-DE');
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
    
    /**
     * @depends testInit
     */
	public function testLocalization(Structure $structure) {
		$this->assertEquals('Startseite', $structure->localized[0]->name);
    }
}
