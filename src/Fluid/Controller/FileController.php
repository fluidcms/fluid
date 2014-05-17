<?php
namespace Fluid\Controller;

use Fluid\Registry;
use Fluid\Controller;
use Fluid\File\FileCollection;
use Fluid\File\FileEntity;
use Fluid\File\FilePreview;
use Fluid\Helper\SessionHelper;
use Fluid\Helper\SessionHelperInterface;
use Fluid\Response;
use Fluid\StaticFile;

class FileController extends Controller implements SessionHelperInterface
{
    use SessionHelper;

    /**
     *
     */
    public function getAll()
    {
        if ($this->validSession()) {
            $registry = new Registry();
            $registry->setStorage($this->getStorage());
            $registry->setXmlMappingLoader($this->getXmlMappingLoader());

            $this->getResponse()->setJson(new FileCollection($registry));
            return;
        }
        $this->getResponse()->setCode(Response::RESPONSE_CODE_FORBIDDEN);
    }

    public function upload()
    {
        if ($this->validSession()) {
            $uploadedFile = $this->getRequest()->getFile();
            if (null !== $uploadedFile) {
                $file = new FileEntity($this->getRegistry());
                $file->setName($uploadedFile['name']);
                $file->getMapper()->persist($file, $uploadedFile);
                $this->getResponse()->setJson($file);
                return;
            }
            $this->getResponse()->setCode(Response::RESPONSE_CODE_BAD_REQUEST);
            return;
        }
        $this->getResponse()->setCode(Response::RESPONSE_CODE_FORBIDDEN);
    }

    public function preview($id)
    {
        if ($this->validSession()) {
            $fileCollection = new Filecollection($this->getRegistry());
            $file = $fileCollection->find($id);

            $preview = new FilePreview($this->getRegistry(), $file);

            new StaticFile($preview->createPreview(), "image/png", true);
            return;
        }
        $this->getResponse()->setCode(Response::RESPONSE_CODE_FORBIDDEN);
    }
}