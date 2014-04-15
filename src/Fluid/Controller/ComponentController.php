<?php
namespace Fluid\Controller;

use Fluid\Component\ComponentCollection;
use Fluid\Controller;
use Fluid\Helper\SessionHelper;
use Fluid\Helper\SessionHelperInterface;
use Fluid\Response;

class ComponentController extends Controller implements SessionHelperInterface
{
    use SessionHelper;

    /**
     *
     */
    public function getAll()
    {
        if ($this->validSession()) {
            //$map = $this->getFluid()->getMap();
            //$this->getResponse()->json($map->toArray());

            $components = new ComponentCollection($this->getStorage(), $this->getXmlMappingLoader());

            $this->getResponse()->json($components->toArray());
            return;
        }
        $this->getResponse()->setCode(Response::RESPONSE_CODE_FORBIDDEN);
    }
}