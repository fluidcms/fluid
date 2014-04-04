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