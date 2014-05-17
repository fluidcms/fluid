<?php
namespace Fluid\File\Renderer;

use Fluid\File\FileEntity;
use Fluid\File\FileMapper;
use Fluid\RegistryInterface;
use Fluid\StaticFile;

class RenderImage implements RendererInterface
{
    /**
     * @var FileEntity
     */
    private $file;

    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @param RegistryInterface $registry
     * @param FileEntity $file
     */
    public function __construct(RegistryInterface $registry, FileEntity $file)
    {
        $this->setRegistry($registry);
        $this->setFile($file);
    }

    /**
     * @return string
     */
    public function render()
    {
        $storage = $this->getRegistry()->getStorage();
        $filename = FileMapper::FILES_DIRECTORY . DIRECTORY_SEPARATOR . $this->file->getId();
        if ($this->file->hasVersion()) {
            // todo $filename .= DIRECTORY_SEPARATOR . $this->file->getVersion();
        }
        $filename .= DIRECTORY_SEPARATOR . $this->file->getName();
        if ($storage->branchFileExists($filename)) {
            $filepath = $storage->getBranchFilename($filename);
            $filetype = null;
            switch(exif_imagetype($filepath)) {
                case IMAGETYPE_GIF:
                    $filetype = 'gif';
                    break;
                case IMAGETYPE_PNG:
                    $filetype = 'png';
                    break;
                case IMAGETYPE_JPEG:
                    $filetype = 'jpeg';
                    break;
            }

            $content = $storage->loadBranchFile($filename);
            new StaticFile($content, $filetype, true);
            return '';
        }
        return null;
    }

    /**
     * @return FileEntity
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param FileEntity $file
     * @return $this
     */
    public function setFile(FileEntity $file)
    {
        $this->file = $file;
        return $this;
    }

    /**
     * @return RegistryInterface
     */
    public function getRegistry()
    {
        return $this->registry;
    }

    /**
     * @param RegistryInterface $registry
     * @return $this
     */
    public function setRegistry(RegistryInterface $registry)
    {
        $this->registry = $registry;
        return $this;
    }
}