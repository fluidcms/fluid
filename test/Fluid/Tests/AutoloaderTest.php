<?php

namespace Fluid\Tests;

use Fluid, PHPUnit_Framework_TestCase;

class AutoloaderTest extends PHPUnit_Framework_TestCase {
	public function testAutoload() {
		$this->assertNull(Fluid\Autoloader::autoload('Foo'), 'Fluid\\Autoloader::autoload() is trying to load classes outside of the Fluid namespace');
		$this->assertNotNull(Fluid\Autoloader::autoload('Fluid\\Fluid'), 'Fluid\Autoloader::autoload() failed to autoload the Fluid\\Fluid class');
	}
}
