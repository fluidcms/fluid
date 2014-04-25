<?php
namespace Fluid;

use Fluid\Exception\PermissionDeniedException;

class Storage implements StorageInterface
{
    const DATA_DIR_NAME = 'data';

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->setConfig($config);
    }

    /**
     * @param string $filename
     * @param bool $useBranch
     * @return string|bool
     * @throws PermissionDeniedException
     */
    protected function createFile($filename, $useBranch = true)
    {
        if ($useBranch) {
            $dir = $this->getConfig()->getStorage() . DIRECTORY_SEPARATOR . $this->getConfig()->getBranch();
        } else {
            $dir = $this->getConfig()->getStorage() . DIRECTORY_SEPARATOR . self::DATA_DIR_NAME;
        }

        if (file_exists($file = $dir . DIRECTORY_SEPARATOR . $filename)) {
            return realpath($file);
        }

        if (!file_exists($dir)) {
            if (!mkdir($dir, 0777, true)) {
                throw new PermissionDeniedException('You do not have read/write permssions on the storage directory');
            }
        }

        if (!file_exists($file)) {
            if (file_put_contents($file, '') === false) {
                throw new PermissionDeniedException('You do not have read/write permssions on the storage directory');
            }
        }
        return realpath($file);
    }

    /**
     * @param string $dir
     * @param bool $useBranch
     * @return array
     */
    protected function scanDir($dir, $useBranch = true)
    {
        if ($useBranch) {
            $dir = $this->getConfig()->getStorage() . DIRECTORY_SEPARATOR . $this->getConfig()->getBranch() . DIRECTORY_SEPARATOR . $dir;
        } else {
            $dir = $this->getConfig()->getStorage() . DIRECTORY_SEPARATOR . self::DATA_DIR_NAME . DIRECTORY_SEPARATOR . $dir;
        }

        $retval = [];
        if (is_dir($dir)) {
            foreach (scandir($dir) as $file) {
                if ($file !== '.' && $file !== '..') {
                    $retval[] = $dir . DIRECTORY_SEPARATOR . $file;
                }
            }
        }
        return $retval;
    }

    /**
     * @return string
     */
    public function getBranchDir()
    {
        return $this->getConfig()->getStorage() . DIRECTORY_SEPARATOR . $this->getConfig()->getBranch();
    }

    /**
     * @return string
     */
    public function getDir()
    {
        return $this->getConfig()->getStorage() . DIRECTORY_SEPARATOR . self::DATA_DIR_NAME;
    }

    /**
     * @param string $filename
     * @return array
     */
    public function loadBranchData($filename)
    {
        $file = $this->createFile($filename, true);
        return json_decode(file_get_contents($file), true);
    }

    /**
     * @param string $filename
     * @return array
     */
    public function loadData($filename)
    {
        $file = $this->createFile($filename, false);
        return json_decode(file_get_contents($file), true);
    }

    /**
     * @param string $filename
     * @param array $data
     * @return bool
     */
    public function saveBranchData($filename, array $data)
    {
        $file = $this->createFile($filename, true);
        return file_put_contents($file, json_encode($data));
    }

    /**
     * @param string $filename
     * @param array $data
     * @return bool
     */
    public function saveData($filename, array $data)
    {
        $file = $this->createFile($filename, false);
        $result = file_put_contents($file, json_encode($data));
        if ($result === false) {
            return false;
        }
        return true;
    }

    /**
     * @param string $dir
     * @return array
     */
    public function getBranchFileList($dir)
    {
        return $this->scanDir($dir, true);
    }

    /**
     * @param string $dir
     * @return array
     */
    public function getFileList($dir)
    {
        return $this->scanDir($dir, false);
    }

    /**
     * @param string $file
     * @return bool
     */
    public function branchFileExists($file)
    {
        $file = $this->getConfig()->getStorage() . DIRECTORY_SEPARATOR . $this->getConfig()->getBranch() . DIRECTORY_SEPARATOR . $file;
        return file_exists($file);
    }

    /**
     * @param string $file
     * @return bool
     */
    public function fileExists($file)
    {
        $file = $this->getConfig()->getStorage() . DIRECTORY_SEPARATOR . self::DATA_DIR_NAME . DIRECTORY_SEPARATOR . $file;
        return file_exists($file);
    }

    /**
     * @param string $tmpfile
     * @param string $newfile
     * @return bool
     * @throws PermissionDeniedException
     */
    public function uploadBranchFile($tmpfile, $newfile)
    {
        $newfile = $this->getConfig()->getStorage() . DIRECTORY_SEPARATOR . $this->getConfig()->getBranch() . DIRECTORY_SEPARATOR . $newfile;
        $dir = dirname($newfile);

        if (!file_exists($dir)) {
            if (!mkdir($dir, 0777, true)) {
                throw new PermissionDeniedException('You do not have read/write permssions on the storage directory');
            }
        }

        return move_uploaded_file($tmpfile, $newfile);
    }

    /**
     * @param string $tmpfile
     * @param string $newfile
     * @return bool
     * @throws PermissionDeniedException
     */
    public function uploadFile($tmpfile, $newfile)
    {
        $newfile = $this->getConfig()->getStorage() . DIRECTORY_SEPARATOR . self::DATA_DIR_NAME . DIRECTORY_SEPARATOR . $newfile;
        $dir = dirname($newfile);

        if (!file_exists($dir)) {
            if (!mkdir($dir, 0777, true)) {
                throw new PermissionDeniedException('You do not have read/write permssions on the storage directory');
            }
        }

        return move_uploaded_file($tmpfile, $newfile);
    }

    /**
     * @param ConfigInterface $config
     * @return $this
     */
    public function setConfig(ConfigInterface $config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @return ConfigInterface
     */
    public function getConfig()
    {
        return $this->config;
    }
}