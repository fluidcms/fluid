<?php
namespace Fluid\Tests\Map;

use Fluid\User\UserCollection;
use Fluid\User\UserMapper;
use Fluid\Tests\Helper;
use PHPUnit_Framework_TestCase;
use Fluid\Fluid;
use Fluid\Storage;

class UserMapperTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Helper::createData();
    }

    public function tearDown()
    {
        Helper::destroy();
    }

    public function testConstruct()
    {
        $userMapper = new UserMapper(new Storage(new Fluid));
        $this->assertInstanceOf('Fluid\StorageInterface', $userMapper->getStorage());
    }

    public function testMap()
    {
        $fluid = new Fluid;
        $fluid->getConfig()->setStorage(Helper::getStorage());
        $userMapper = new UserMapper($storage = new Storage($fluid));

        $users = new UserCollection($storage);
        $userMapper->mapCollection($users);

        $this->assertCount(2, $users);

        /** @var \Fluid\User\UserEntity $user */
        $user = $users[0];
        $this->assertEquals(1, $user->getId());
        $this->assertEquals('test@test.com', $user->getEmail());
        $this->assertNotEmpty($user->getPassword());

        $user = $users[1];
        $this->assertEquals(2, $user->getId());
        $this->assertEquals('test2@test2.com', $user->getEmail());
        $this->assertNotEmpty($user->getPassword());
    }
}