<?php
namespace Fluid\Tests;

use PHPUnit_Framework_TestCase;
use Fluid\Config;

class ConfigTest extends PHPUnit_Framework_TestCase
{
    public function testConfig()
    {
        Config::set('mytest', 'myvalue');
        $this->assertEquals('myvalue', Config::get('mytest'));
    }

    public function testTestsConfigOverride()
    {
        Config::set('websocket', 42);
        $this->assertNotEquals(42, Config::get('websocket'));
    }
}