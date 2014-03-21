<?php
namespace Fluid\Tests\Map;

use Fluid\User\UserCollection;
use Fluid\Tests\Helper;
use Fluid\User\UserEntity;
use PHPUnit_Framework_TestCase;
use Fluid\Fluid;
use Fluid\Storage;

class UserCollectionTest extends PHPUnit_Framework_TestCase
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
        $users = new UserCollection(new Storage(new Fluid));
        $this->assertInstanceOf('Fluid\StorageInterface', $users->getStorage());
        $this->assertInstanceOf('Fluid\User\UserMapper', $users->getUserMapper());
    }

    public function testCollection()
    {
        $fluid = new Fluid;
        $fluid->getConfig()->setStorage(Helper::getStorage());
        $users = new UserCollection($fluid->getStorage());
        $this->assertCount(2, $users);
        $this->assertTrue(isset($users[0]));
        $this->assertTrue(isset($users[1]));
        foreach ($users as $user) {
            $this->assertInstanceOf('Fluid\User\UserEntity', $user);
        }
    }

    public function testAdd()
    {
        $users = new UserCollection(new Storage(new Fluid));
        $users->add(new UserEntity);
        $this->assertInstanceOf('Fluid\User\UserEntity', $users[0]);
    }

    public function testFindBy()
    {
        $fluid = new Fluid;
        $fluid->getConfig()->setStorage(Helper::getStorage());
        $users = new UserCollection($fluid->getStorage());

        $user = $users->findBy(['id' => 1]);
        $this->assertInstanceOf('Fluid\User\UserEntity', $user[0]);
        $this->assertEquals('test@test.com', $user[0]->getEmail());
    }

    public function testFindOneBy()
    {
        $fluid = new Fluid;
        $fluid->getConfig()->setStorage(Helper::getStorage());
        $users = new UserCollection($fluid->getStorage());

        $user = $users->findOneBy(['id' => 1]);
        $this->assertInstanceOf('Fluid\User\UserEntity', $user);
        $this->assertEquals('test@test.com', $user->getEmail());

        $user = $users->findOneBy(['email' => 'test@test.com']);
        $this->assertInstanceOf('Fluid\User\UserEntity', $user);
        $this->assertEquals('test@test.com', $user->getEmail());

        $user = $users->findOneBy(['email' => 'test@test.com', 'name' => 'Joe Dino']);
        $this->assertInstanceOf('Fluid\User\UserEntity', $user);
        $this->assertEquals('test@test.com', $user->getEmail());
        $this->assertEquals('Joe Dino', $user->getName());

        $user = $users->findOneBy(['email' => 'test2@test2.com']);
        $this->assertInstanceOf('Fluid\User\UserEntity', $user);
        $this->assertEquals('test2@test2.com', $user->getEmail());

        $user = $users->findOneBy(['email' => 'not existant']);
        $this->assertNull($user);
    }
}