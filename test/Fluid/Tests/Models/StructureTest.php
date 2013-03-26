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
			$this->assertArrayHasKey('page', $page);
		}
	}
	
	/**
	 * @depends testInit
	 */
	public function testLocalization(Structure $structure) {		
		$this->assertEquals('Startseite', $structure->getLocalized()[0]['name']);
		$this->assertEquals('Kontaktformular', $structure->getLocalized()[3]['pages'][0]['name']);
		$this->assertEquals('', $structure->getLocalized()[1]['name']);
		$this->assertFalse(isset($structure->getLocalized()[4]));
	}
}
