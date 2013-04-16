<?php

namespace Fluid\Tests\Admin;

use Fluid\Fluid, Fluid\Admin\Router, PHPUnit_Framework_TestCase;

class RouterTest extends PHPUnit_Framework_TestCase {	
	public function testPublicFile() {
		ob_start();
		Router::route('images/background.gif');
		$actual = ob_get_clean();
		ob_end_clean();
		
		$this->assertRegExp('/GIF/', $actual);
	}
	
	public function testModels() {
		// Test structure
		$actual = Router::route('structure.json');
		$this->assertRegExp('/^[\[|{]/', $actual);

		// Test page
		$_POST = require __DIR__ . "/../Fixtures/request/page_request.php";
		$actual = Router::route('page.json');
		$this->assertRegExp('/^[\[|{]/', $actual);
		
		// Test languages
		$actual = Router::route('languages.json');
		$this->assertRegExp('/^[\[|{]/', $actual);

		// Test page token
		// !! Requires database connenction
		//$actual = Router::route('pagetoken.json');
		//$this->assertRegExp('/^[\[|{]/', $actual);		
	}
}
