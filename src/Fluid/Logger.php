<?php
namespace Fluid;

use Psr\Log\LoggerInterface;

/**
 * Class Log
 * @package Fluid\Debug
 */
class Logger implements LoggerInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var string
     */
    private $content = '';

    /**
     * @var string
     */
    private $file;

    /**
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->setConfig($config);
    }

    public function __destruct()
    {
        if ($this->getConfig()->getDebug()) {
            $this->writeLog();
        }
    }

    private function writeLog()
    {
        if (!empty($this->content)) {
            if ($this->file === null) {
                $this->file = $this->getConfig()->getLog();
            }

            $content = '';
            if (file_exists($this->file)) {
                $content = file_get_contents($this->file);
            }

            $content .= $this->content;

            file_put_contents($this->file, $content);
        }
    }

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function emergency($message, array $context = [])
    {

    }

    /**
     * Action must be taken immediately.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function alert($message, array $context = [])
    {

    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function critical($message, array $context = [])
    {

    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function error($message, array $context = [])
    {
        $this->debug($message);
        $this->writeLog();
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function warning($message, array $context = [])
    {

    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function notice($message, array $context = [])
    {

    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function info($message, array $context = [])
    {

    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function debug($message, array $context = [])
    {
        if ($this->getConfig()->getDebug()) {
            $this->content .= date('Y-m-d H:i:s') . " {$message}" . PHP_EOL;
        }
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     */
    public function log($level, $message, array $context = [])
    {

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