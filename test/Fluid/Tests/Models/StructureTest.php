<?php

namespace Fluid\Tests\Models;

use Fluid\Fluid, Fluid\Models\Structure, PHPUnit_Framework_TestCase;

class StructureTest extends PHPUnit_Framework_TestCase {
	public function setUp() {
		Fluid::setLanguage('de-DE');
		copy(__DIR__."/../Fixtures/storage/structure/structure_master.json", __DIR__."/../Fixtures/storage/structure/structure_master.json.original");
		foreach(Fluid::getConfig('languages') as $language) {
			copy(__DIR__."/../Fixtures/storage/structure/structure_{$language}.json", __DIR__."/../Fixtures/storage/structure/structure_{$language}.json.original");
		}
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
	
	public function testSave() {
		Fluid::setLanguage('en-US');
		$content = file_get_contents(__DIR__."/../Fixtures/request/new_structure.json");
		Structure::save($content);
		
		$structure = new Structure();
		$this->assertEquals('form', $structure->pages[2]['page']);
		
		$this->assertEquals('Contact Form Success', $structure->getLocalized()[3]['pages'][0]['name']);
	}
	
	public function tearDown() {
		rename(__DIR__."/../Fixtures/storage/structure/structure_master.json.original", __DIR__."/../Fixtures/storage/structure/structure_master.json");
		foreach(Fluid::getConfig('languages') as $language) {
			rename(__DIR__."/../Fixtures/storage/structure/structure_{$language}.json.original", __DIR__."/../Fixtures/storage/structure/structure_{$language}.json");
		}
	}
}
