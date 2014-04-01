<?php
namespace Fluid\Controller;

use Fluid\Controller;
use Fluid\Helper\SessionHelper;
use Fluid\Helper\SessionHelperInterface;
use Fluid\Response;

class PageController extends Controller implements SessionHelperInterface
{
    use SessionHelper;

    public function getAll()
    {
        if ($this->validSession()) {
            $map = $this->getFluid()->getMap();
            $this->getResponse()->json($map->toArray());
        }
        $this->getResponse()->setCode(Response::RESPONSE_CODE_FORBIDDEN);
    }

    public function get($page)
    {
        $this->getResponse()->json($page);
    }
}