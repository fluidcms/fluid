<?php

namespace Fluid\File;

use Exception;
use Fluid\Fluid;

/**
 * File model
 *
 * @package fluid
 */
class File
{
    private $filePath;

    private $id;
    private $src;
    private $name;
    private $width;
    private $height;
    private $type;
    private $size;
    private $creation;

    /**
     * Init
     *
     * @param string|null $id
     */
    public function __construct($id = null)
    {
        $this->id = $id;

        if ($file = self::getFileInfo($id)) {
            $this->src = $file['src'];
            $this->name = $file['name'];
            $this->width = $file['width'];
            $this->height = $file['height'];
            $this->type = $file['type'];
            $this->size = $file['size'];
            $this->creation = $file['creation'];
        }
    }

    /**
     * Check if dir exists or create it
     *
     * @return bool
     */
    public static function checkDir()
    {
        if (!is_dir(Fluid::getBranchStorage() . '/files/')) {
            return mkdir(Fluid::getBranchStorage() . '/files/');
        }
        return true;
    }

    /**
     * Get a file by id
     *
     * @param string $id
     * @return self
     */
    public static function get($id)
    {
        return new self($id);
    }

    /**
     * Get a file's path
     *
     * @throws Exception
     * @return string
     */
    public function getPath()
    {
        if (isset($this->filePath)) {
            return $this->filePath;
        }

        self::checkDir();
        $dir = Fluid::getBranchStorage() . "/files/{$this->id}";
        foreach (scandir($dir) as $file) {
            if ($file !== '.' && $file !== '..' && is_file("{$dir}/{$file}")) {
                $this->filePath = "{$dir}/{$file}";
                return $this->filePath;
                break;
            }
        }

        throw new Exception('File does not exists');
    }

    /**
     * Get a file's info
     *
     * @return array
     */
    public function getInfo()
    {
        return array(
            'id' => $this->id,
            'src' => $this->src,
            'name' => $this->name,
            'width' => $this->width,
            'height' => $this->height,
            'type' => $this->type,
            'size' => $this->size,
            'creation' => $this->creation
        );
    }

    /**
     * Get all files
     *
     * @return array
     */
    public static function getFiles()
    {
        self::checkDir();
        $output = array();
        $sort = array();
        foreach (scandir(Fluid::getBranchStorage() . '/files') as $id) {
            if ($id !== '.' && $id !== '..' && strlen($id) === 8 && ctype_alnum($id)) {
                if ($file = self::getFileInfo($id)) {
                    $output[] = $file;
                    $sort[] = $file['creation'];
                }
            }
        }

        // Sort by creation date
        $retval = array();
        arsort($sort);
        foreach ($sort as $key => $value) {
            $retval[] = $output[$key];
        }

        return $retval;
    }

    /**
     * Scan a file directory for the file and get the file's info
     *
     * @param string $id
     * @return array
     */
    private static function getFileInfo($id)
    {
        $dir = Fluid::getBranchStorage() . "/files/{$id}";

        foreach (scandir($dir) as $file) {
            if ($file !== '.' && $file !== '..' && is_file("{$dir}/{$file}")) {
                if ($file = FileInfo::getImageInfo("{$dir}/{$file}")) {
                    return array_merge(
                        array("id" => $id, 'src' => "/fluidcms/images/{$id}/{$file['name']}"),
                        $file
                    );
                }
            }
        }
        return null;
    }

    /**
     * Delete file
     *
     * @param string $id
     * @return bool
     */
    public static function delete($id)
    {
        self::checkDir();
        $dir = scandir(Fluid::getBranchStorage() . '/files/');
        foreach ($dir as $file) {
            if (substr($file, 0, 8) === $id) {
                unlink(Fluid::getBranchStorage() . '/files/' . $file);
                return true;
            }
        }

        return false;
    }


    /**
     * Make an image preview
     *
     * @return array
     */
    public function getPreview()
    {
        self::checkDir();
        return array(
            'image' => base64_encode(FilePreview::make($this))
        );
    }

    /**
     * Upload file
     *
     * @param string $id
     * @param array $file
     * @return array
     */
    public static function upload($id, array $file)
    {
        self::checkDir();
        if (!$file['error'] && strlen($id) === 8 && self::idIsUnique($id)) {
            $file = FileInfo::getTmpFileInfo($file);
            if ($file['size'] <= 2097152) {
                if (!is_dir(Fluid::getBranchStorage() . "/files")) {
                    mkdir(Fluid::getBranchStorage() . "/files");
                }
                if (!is_dir(Fluid::getBranchStorage() . "/files/{$id}")) {
                    mkdir(Fluid::getBranchStorage() . "/files/{$id}");
                }

                rename($file["tmp_name"], Fluid::getBranchStorage() . "/files/{$id}/{$file['name']}");
                unset($file["tmp_name"]);
                return $id;
            }
        }
        return null;
    }

    /**
     * Check if file uploaded id is unique
     *
     * @param string $id
     * @return bool
     */
    public static function idIsUnique($id)
    {
        self::checkDir();
        $dir = scandir(Fluid::getBranchStorage() . '/files/');
        foreach ($dir as $file) {
            if (substr($file, 0, 8) === $id) {
                return false;
            }
        }

        return true;
    }
}