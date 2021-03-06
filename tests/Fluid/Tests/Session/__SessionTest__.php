<?php
namespace Fluid\Tests\Session;

use Fluid;
use PHPUnit_Framework_TestCase;
use Fluid\Tests\Helper;

class SessionTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Helper::init();
    }

    public function tearDown()
    {
        Helper::destroy();
    }

    public function testCreateSession()
    {
        $session = Fluid\Session\Session::create();

        $validate = Fluid\Session\Session::validate($session);

        $this->assertEquals(64, strlen($session));
        $this->assertTrue($validate);
    }
}
