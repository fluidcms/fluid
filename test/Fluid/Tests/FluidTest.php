<?php

namespace Fluid\Tests;

use Fluid\Fluid, PHPUnit_Framework_TestCase;

class FluidTest extends PHPUnit_Framework_TestCase {
	public function testLanguage() {
		$this->assertEquals('en-US', Fluid::getLanguage());		
	}
	
	public function testConfig() {		
		Fluid::setConfig('database', 'foo');
		$this->assertEquals('foo', Fluid::getConfig('database'));		
		
		// Storage config will add a / at the end of the config
		Fluid::setConfig('storage', 'foo');
		$this->assertEquals('foo/', Fluid::getConfig('storage'));		
	}
}
