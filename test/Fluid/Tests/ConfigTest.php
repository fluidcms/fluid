<?php
namespace Fluid\Tests;

use PHPUnit_Framework_TestCase;
use Fluid\Config;

class ConfigTest extends PHPUnit_Framework_TestCase
{
    public function testSettersAndGetters()
    {
        $config = new Config;
        $config->setBranch('my_branch');
        $config->setLanguage('de-DE');
        $config->setLanguages(['de-DE' => "German", 'en-US' => "English"]);
        $config->setLog('/path/to/log/file');
        $config->setStorage('/path/to/fluid/storage');
        $config->setStructure('/path/to/fluid/structure');

        $this->assertEquals('my_branch', $config->getBranch());
        $this->assertEquals('de-DE', $config->getLanguage());
        $this->assertEquals(['de-DE' => "German", 'en-US' => "English"], $config->getLanguages());
        $this->assertEquals('/path/to/log/file', $config->getLog());
        $this->assertEquals('/path/to/fluid/storage', $config->getStorage());
        $this->assertEquals('/path/to/fluid/structure', $config->getStructure());
    }
}