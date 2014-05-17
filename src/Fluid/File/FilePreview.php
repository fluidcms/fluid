<?php

namespace Fluid\File;

use DomainException;
use Fluid\RegistryInterface;
use Fluid\StorageInterface;

class FilePreview
{
    const MAX_SIZE = 82;

    /**
     * @var FileEntity
     */
    private $file;

    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @param RegistryInterface $registry
     * @param FileEntity $file
     * @throws DomainException
     */
    public function __construct(RegistryInterface $registry, FileEntity $file)
    {
        $this->file = $file;
        $this->setStorage($registry->getStorage());
    }

    /**
     * Create the preview
     *
     * @throws DomainException
     * @return string
     */
    public function createPreview()
    {
        $filepath = $this->getStorage()->getBranchDir() . DIRECTORY_SEPARATOR .
            FileMapper::FILES_DIRECTORY . DIRECTORY_SEPARATOR .
            $this->file->getId() . DIRECTORY_SEPARATOR . $this->file->getName();

        $max = self::MAX_SIZE;
        $size = getimagesize($filepath);
        $width = $size[0];
        $height = $size[1];

        if ($width > $height) {
            if ($width > $max) {
                $height *= $max / $width;
                $width = $max;
            }
        } else {
            if ($height > $max) {
                $width *= $max / $height;
                $height = $max;
            }
        }
        $width = (int)round($width);
        $height = (int)round($height);

        if ($size['mime'] === "image/jpeg") {
            $img = imagecreatefromjpeg($filepath);
        } else if ($size['mime'] === "image/png") {
            $img = imagecreatefrompng($filepath);
        } else if ($size['mime'] === "image/gif") {
            $img = imagecreatefromgif($filepath);
        } else {
            throw new DomainException('Unknown image type: ' . $size['mime']);
        }

        $newImg = imagecreatetruecolor($width, $height);

        imagealphablending($newImg, false);
        imagesavealpha($newImg, true);

        // Crop and resize image.
        imagecopyresampled($newImg, $img, 0, 0, 0, 0, $width, $height, $size[0], $size[1]);
        imagedestroy($img);

        $tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid();
        imagepng($newImg, $tmp, 9);
        $retval = file_get_contents($tmp);
        unlink($tmp);

        imagedestroy($newImg);

        return $retval;
    }

    /**
     * @return StorageInterface
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @param StorageInterface $storage
     * @return $this
     */
    public function setStorage(StorageInterface $storage)
    {
        $this->storage = $storage;
        return $this;
    }
}
