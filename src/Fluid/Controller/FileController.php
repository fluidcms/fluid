<?php
namespace Fluid\Controller;

use Fluid\Container;
use Fluid\Controller;
use Fluid\File\FileCollection;
use Fluid\Helper\SessionHelper;
use Fluid\Helper\SessionHelperInterface;
use Fluid\Response;

class FileController extends Controller implements SessionHelperInterface
{
    use SessionHelper;

    /**
     *
     */
    public function getAll()
    {
        if ($this->validSession()) {
            $container = new Container();
            $container->setStorage($this->getStorage());
            $container->setXmlMappingLoader($this->getXmlMappingLoader());

            $this->getResponse()->setJson(new FileCollection($container));
            return;
        }
        $this->getResponse()->setCode(Response::RESPONSE_CODE_FORBIDDEN);
    }
}