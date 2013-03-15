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
}
