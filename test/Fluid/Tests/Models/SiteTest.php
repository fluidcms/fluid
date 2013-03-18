<?php

namespace Fluid\Tests\Models;

use Fluid\Fluid, Fluid\Models\Site, PHPUnit_Framework_TestCase;

class SiteTest extends PHPUnit_Framework_TestCase {
	public function testInit() {
		$site = new Site();
		$this->assertInternalType('string', $site->data['name']);
	}
}
