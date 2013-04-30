<?php

namespace Fluid\Tests;

use Fluid, Fluid\View, PHPUnit_Framework_TestCase;

class PageTest extends PHPUnit_Framework_TestCase
{
    public function testPage()
    {
        View::setTemplatesDir(__DIR__ . '/Fixtures/templates/');

        $view = View::create(
            'page.twig',
            array('page' => array('title' => 'Hello World'))
        );

        $this->assertRegExp('{<html>.?Hello World.?</html>}msU', $view);
    }
}
