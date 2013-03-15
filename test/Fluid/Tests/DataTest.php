<?php

namespace Fluid\Tests;

use Fluid, Fluid\Data, PHPUnit_Framework_TestCase;

class DataTest extends PHPUnit_Framework_TestCase {
	public function setUp() {
		Data::setStructure(new Fluid\Models\Structure());
    }
	
	public function testGetStructure() {
		$this->assertInstanceOf('Fluid\Models\Structure', Data::getStructure());
    }
    
	public function testGet() {
		$data = Data::get('contact/form');
		
		$this->assertGreaterThan(0, sizeof($data['structure']));
		$this->assertStringMatchesFormat('%a', $data['page']['title']);
		$this->assertStringMatchesFormat('%a', $data['page']['content']);
		
		return $data;
    }
    
	public function testPageParents() {
		$data = Data::get('contact/form/test');
		
		$this->assertGreaterThan(0, sizeof($data['parents']));
		$this->assertStringMatchesFormat('%a', $data['parent']['parent']['content']);
	}
}
