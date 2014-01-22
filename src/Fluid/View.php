<?php

namespace Fluid;

use Fluid\Map\Map;
use Fluid\Layout\Layout;

/**
 * View class
 *
 * @package fluid
 */
class View
{
    /** @var \Fluid\Fluid */
    private $fluid;

    /** @var \Fluid\Map\Map */
    private $map;

    /** @var \Fluid\Layout\Layout */
    private $layout;

    /**
     * @param \Fluid\Fluid $fluid
     * @param \Fluid\Map\Map $map
     * @param \Fluid\Layout\Layout $layout
     */
    public function __construct(Fluid $fluid, Map $map, Layout $layout)
    {
        $this->setFluid($fluid);
        $this->setMap($map);
        $this->setLayout($layout);
    }

    /**
     * @param array $page
     * @param array $data
     * @return string
     */
    public function load(array $page, array $data)
    {
        $file = $this->getLayout()->getConfig()->getFile();
        return $this->getFluid()->getTemplateEngine()->render($file, $data, $this->getLayout()->getConfig());
    }

    /**
     * @param \Fluid\Fluid $fluid
     * @return $this
     */
    public function setFluid(Fluid $fluid)
    {
        $this->fluid = $fluid;
        return $this;
    }

    /**
     * @return \Fluid\Fluid
     */
    public function getFluid()
    {
        return $this->fluid;
    }

    /**
     * @param \Fluid\Layout\Layout $layout
     * @return $this
     */
    public function setLayout(Layout $layout)
    {
        $this->layout = $layout;
        return $this;
    }

    /**
     * @return \Fluid\Layout\Layout
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * @param \Fluid\Map\Map $map
     * @return $this
     */
    public function setMap(Map $map)
    {
        $this->map = $map;
        return $this;
    }

    /**
     * @return \Fluid\Map\Map
     */
    public function getMap()
    {
        return $this->map;
    }
}