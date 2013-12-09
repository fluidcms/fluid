<?php
namespace Fluid\Tests\Branch;

use Fluid;
use PHPUnit_Framework_TestCase;
use Fluid\Tests\Helper;

class InitBranchTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Helper::init();
    }

    public function tearDown()
    {
        Helper::destroy();
    }

    public function testInitBranch()
    {
        Fluid\Branch\Branch::init('develop');

        $this->assertFileExists(Helper::getFixtureDir() . "/storage/master/.gitignore");
        $this->assertFileExists(Helper::getFixtureDir() . "/storage/develop/.gitignore");
    }
}
