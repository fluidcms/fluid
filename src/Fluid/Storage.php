<?php
namespace Fluid;

use Fluid\Exception\PermissionDeniedException;

class Storage implements StorageInterface
{
    const DATA_DIR_NAME = 'data';

    /**
     * @var Fluid
     */
    private $fluid;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @param Fluid $fluid
     */
    public function __construct(Fluid $fluid)
    {
        $this->setFluid($fluid);
        $this->setConfig($fluid->getConfig());
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
     * @param Fluid $fluid
     * @return $this
     */
    public function setFluid(Fluid $fluid)
    {
        $this->fluid = $fluid;
        return $this;
    }

    /**
     * @return Fluid
     */
    public function getFluid()
    {
        return $this->fluid;
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

    /**
     * Save data to storage
     *
     * @param string $content
     * @param mixed|null $file
     */
    public static function save($content, $file = null)
    {
        if (null === $file) {
            $file = static::$dataFile;
        }

        $dir = Fluid::getBranchStorage() . '/' . dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir);
        }

        $file = Fluid::getBranchStorage() . '/' . $file;

        file_put_contents($file, $content);

        //self::storeCache($content);
    }

    /**
     * Set the data file
     *
     * @param string
     */
    public static function setDataFile($file)
    {
        static::$dataFile = $file;
    }

    /**
     * Get the data file
     *
     * @return string
     */
    public static function getDataFile()
    {
        return static::$dataFile;
    }
}