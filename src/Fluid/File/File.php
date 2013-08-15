<?php

namespace Fluid\File;

use Exception,
    Fluid\Fluid,
    Fluid\File\FileInfo,
    Fluid\File\FilePreview;

/**
 * File model
 *
 * @package fluid
 */
class File
{
    private $id;
    private $filePath;

    /**
     * Init
     *
     * @param   string  $id
     */
    public function __construct($id = null)
    {
        $this->id = $id;
    }

    /**
     * Check if dir exists or create it
     *
     * @return  bool
     */
    public static function checkDir()
    {
        if (!is_dir(Fluid::getBranchStorage() . 'files/')) {
            return mkdir(Fluid::getBranchStorage() . 'files/');
        } else {
            return true;
        }
    }

    /**
     * Get a file by id
     *
     * @param   string  $id
     * @return  self
     */
    public static function get($id)
    {
        return new self($id);
    }

    /**
     * Get a file path
     *
     * @throws  Exception
     * @return  string
     */
    public function getPath()
    {
        if (isset($this->filePath)) {
            return $this->filePath;
        }

        self::checkDir();
        $dir = Fluid::getBranchStorage() . "files/{$this->id}";
        foreach(scandir($dir) as $file) {
            if ($file !== '.' && $file !== '..' && is_file("{$dir}/{$file}")) {
                $this->filePath = "{$dir}/{$file}";
                return $this->filePath;
                break;
            }
        }

        throw new Exception('File does not exists');
    }

    /**
     * Get all files
     *
     * @return  array
     */
    public static function getFiles()
    {
        self::checkDir();
        $output = array();
        $sort = array();
        foreach (scandir(Fluid::getBranchStorage() . 'files') as $id) {
            if ($id !== '.' && $id !== '..' && strlen($id) === 8 && ctype_alnum($id)) {
                $dir = Fluid::getBranchStorage() . "files/{$id}";
                foreach(scandir($dir) as $file) {
                    if ($file !== '.' && $file !== '..' && is_file("{$dir}/{$file}")) {
                        if ($file = FileInfo::getImageInfo("{$dir}/{$file}")) {
                            $output[] = array_merge(
                                array("id" => $id, 'src' => "/fluidcms/files/{$id}/{$file['name']}"),
                                $file
                            );
                            $sort[] = $file['creation'];
                            break;
                        }
                    }
                }
            }
        }

        // Sort by creation date
        $retval = array();
        arsort($sort);
        foreach($sort as $key => $value) {
            $retval[] = $output[$key];
        }

        return $retval;
    }

    /**
     * Save file
     *
     * @return  array
     */
    public static function save()
    {
        self::checkDir();
        if ($file = self::upload()) {
            return $file;
        }
        return null;
    }

    /**
     * Delete file
     *
     * @param   string  $id
     * @return  bool
     */
    public static function delete($id)
    {
        self::checkDir();
        $dir = scandir(Fluid::getBranchStorage() . 'files/');
        foreach ($dir as $file) {
            if (substr($file, 0, 8) === $id) {
                unlink(Fluid::getBranchStorage() . 'files/' . $file);
                return true;
            }
        }

        return false;
    }


    /**
     * Make an image preview
     *
     * @return  array
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
     * @return  array
     */
    public static function upload()
    {
        self::checkDir();
        foreach ($_FILES as $file) {
            if (!$file['error'] && isset($_POST['id']) && strlen($_POST['id']) === 8 && self::idIsUnique($_POST['id'])) {
                $file = FileInfo::getTmpFileInfo($file);
                if ($file['size'] <= 2097152) {
                    rename($file["tmp_name"], Fluid::getBranchStorage() . 'files/' . $_POST['id'] . '_' . $file['name']);
                    unset($file["tmp_name"]);
                    return array_merge(array('id' => $_POST['id'], 'src' => "/fluidcms/files/preview/{$_POST['id']}/{$file['name']}"), $file);
                }
            }
        }
        return null;
    }

    /**
     * Check if file uploaded id is unique
     *
     * @param   string  $id
     * @return  bool
     */
    public static function idIsUnique($id)
    {
        self::checkDir();
        $dir = scandir(Fluid::getBranchStorage() . 'files/');
        foreach ($dir as $file) {
            if (substr($file, 0, 8) === $id) {
                return false;
            }
        }

        return true;
    }
}