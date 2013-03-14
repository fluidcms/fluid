<?php

namespace Fluid\Tests;

use Fluid\Fluid, Fluid\Router, PHPUnit_Framework_TestCase;

class RouterTest extends PHPUnit_Framework_TestCase {
	public function setUp() {
		Fluid::setConfig('storage', __DIR__.'/Fixtures/storage/');
	}
	
	public function testBadRoute() {
		$this->assertEquals(Fluid::NOT_FOUND, Router::route('foo'), 'Function should not find the page');		
    }
	
	/**
	 * @expectedException     Twig_Error_Loader
	 * @expectedExceptionCode 0
	 */
	public function testPageRoute() {
		Router::route('/');
	}
	
	/**
	 * @expectedException     Twig_Error_Loader
	 * @expectedExceptionCode 0
	 */
	public function testPageWithParentRoute() {
		Router::route('/contact/form/');
	}
}
