<?php
namespace Fluid\Controller;

use Fluid\Controller;
use Fluid\Helper\SessionHelper;
use Fluid\Helper\SessionHelperInterface;
use Fluid\Response;

class MapController extends Controller implements SessionHelperInterface
{
    use SessionHelper;

    public function get()
    {
        if ($this->validSession()) {
            $this->getFluid()->getMap();

            $this->getResponse()->json(['user' => $this->getUser()->getName()]);
        }
        $this->getResponse()->setCode(Response::RESPONSE_CODE_FORBIDDEN);
    }
}