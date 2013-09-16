<?php

namespace Fluid\Tests\Components;

use Fluid, PHPUnit_Framework_TestCase, Fluid\Tests\Helper;

class CloneBranchTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Helper::init();
        Helper::createMaster();
        Helper::commitMaster();
    }

    public function tearDown()
    {
        Helper::destroy();
    }

    public function testInitBranch()
    {
        Fluid\Branch\Branch::init('develop');
        $this->assertFileExists(Helper::getStorage() . "/global_en-US.json");
    }
}
