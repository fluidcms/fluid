<?php
namespace Fluid\Tests;

use PHPUnit_Framework_TestCase;
use Fluid\Autoloader;

class AutoloaderTest extends PHPUnit_Framework_TestCase
{
    public function testAutoload()
    {
        $declared = get_declared_classes();
        $declaredCount = count($declared);
        Autoloader::autoload('FooBarClass');
        $this->assertEquals($declaredCount, count(get_declared_classes()), 'Fluid\\Autoloader::autoload() is trying to load classes outside of the Fluid namespace');
        Autoloader::autoload('Fluid\\Fluid');
        $this->assertTrue(in_array('Fluid\\Fluid', get_declared_classes()), 'Fluid\\Autoloader::autoload() failed to autoload the Fluid\\Fluid class');
    }
}