<?php
namespace Fluid\File\Renderer;

use Fluid\File\FileEntity;

interface RendererInterface
{
    /**
     * @return string
     */
    public function render();

    /**
     * @return FileEntity
     */
    public function getFile();

    /**
     * @param FileEntity $file
     * @return $this
     */
    public function setFile(FileEntity $file);
}