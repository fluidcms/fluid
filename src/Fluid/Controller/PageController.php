<?php
namespace Fluid\Controller;

use Fluid\Controller;
use Fluid\Helper\SessionHelper;
use Fluid\Helper\SessionHelperInterface;
use Fluid\Page\PageEntity;
use Fluid\Response;

class PageController extends Controller implements SessionHelperInterface
{
    use SessionHelper;

    /**
     *
     */
    public function getAll()
    {
        if ($this->validSession()) {
            $map = $this->getFluid()->getMap();
            $this->getResponse()->json($map->toArray());
            return;
        }
        $this->getResponse()->setCode(Response::RESPONSE_CODE_FORBIDDEN);
    }

    /**
     * @param $page
     */
    public function get($page)
    {
        if ($this->validSession()) {
            $map = $this->getFluid()->getMap();
            $page = $map->findPage($page);
            if ($page instanceof PageEntity) {
                $this->getResponse()->json($page->toArray());
                return;
            }
            $this->getResponse()->setCode(Response::RESPONSE_CODE_NOT_FOUND);
            return;
        }
        $this->getResponse()->setCode(Response::RESPONSE_CODE_FORBIDDEN);
    }

    /**
     * @param $page
     */
    public function post($page)
    {
        if ($this->validSession()) {
            $map = $this->getFluid()->getMap();
            $page = $map->findPage($page);
            if ($page instanceof PageEntity) {
                $params = $this->request->params(['variables']);
                $page->getVariables()->reset($params['variables'])->persist();
                $this->getResponse()->json($page->toArray());
                return;
            }
            $this->getResponse()->setCode(Response::RESPONSE_CODE_NOT_FOUND);
            return;
        }
        $this->getResponse()->setCode(Response::RESPONSE_CODE_FORBIDDEN);
    }
}