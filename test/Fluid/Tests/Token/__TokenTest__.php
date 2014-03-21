<?php
namespace Fluid\Tests\Token;

use Fluid;
use PHPUnit_Framework_TestCase;
use Fluid\Tests\Helper;

class TokenTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Helper::init();
    }

    public function tearDown()
    {
        Helper::destroy();
    }

    public function testCreateToken()
    {
        $session = Fluid\Token\Token::create();

        $validate = Fluid\Token\Token::validate($session);

        $this->assertEquals(64, strlen($session));
        $this->assertTrue($validate);
    }
}
