<?php

namespace Fluid\Tests\Models;

use Fluid\Fluid, Fluid\Models\Structure, Fluid\Models\Page, PHPUnit_Framework_TestCase;

class PageTest extends PHPUnit_Framework_TestCase {
	public function testInit() {
		$structure = new Structure();
		
		$page = new Page($structure, 'contact/form');
		
		return $page;
	}
	
	/**
	 * @depends testInit
	 */
	public function testPageParent(Page $page) {
		$this->assertTrue($page->hasParent());
		$this->assertInstanceOf('Fluid\Models\Page', $page->parent);
		$this->assertFalse($page->parent->hasParent());
	}
}
