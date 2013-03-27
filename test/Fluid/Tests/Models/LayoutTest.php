<?php

namespace Fluid\Tests\Models;

use Fluid\Fluid, Fluid\View, Fluid\Models\Layout, PHPUnit_Framework_TestCase;

class LayoutTest extends PHPUnit_Framework_TestCase {
	public function setUp() {
		Fluid::setConfig('templates', __DIR__.'/../Fixtures/templates/');		
		Fluid::setConfig('layouts', 'layouts');		
	}
	
	public function testGetLayout() {						
		$actual = Layout::getLayouts();
		
		$this->assertInternalType('array', $actual, 'Function did not return an array');
		$this->assertEquals(array('default', 'home'), $actual);
	}
}
