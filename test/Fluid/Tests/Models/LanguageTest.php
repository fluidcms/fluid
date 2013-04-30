<?php

namespace Fluid\Tests\Models;

use Fluid\Fluid, Fluid\Models\Language, PHPUnit_Framework_TestCase;

class LanguageTest extends PHPUnit_Framework_TestCase
{
    public function testGetLanguage()
    {
        $actual = Language::getLanguages();
        $this->assertInternalType('array', $actual, 'Function did not return an array');
        $this->assertEquals(array('en-US', 'de-DE'), $actual);
    }
}
