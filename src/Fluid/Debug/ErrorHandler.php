<?php

namespace Fluid\Debug;

class ErrorHandler
{
    /**
     * Error handler
     *
     * @param int $errno
     * @param string|null $errstr
     * @param string|null $errfile
     * @param int|null $errline
     * @param array|null $errcontext
     * @return bool
     */
    public static function error($errno, $errstr = null, $errfile = null, $errline = null, array $errcontext = null)
    {
        if (0 !== error_reporting()) {
            self::log($errno, $errstr, $errfile, $errline, $errcontext);
        }

        return false;
    }

    /**
     * Uncatchable error handler
     *
     * @return bool
     */
    public static function shutdown()
    {
        $error = error_get_last();

        if ($error !== NULL && $error["type"] !== E_WARNING && $error["type"] !== E_USER_WARNING && $error["type"] !== E_NOTICE && $error["type"] !== E_USER_NOTICE) {
            self::log($error["type"], $error["message"], $error["file"], $error["line"]);
        }

        return false;
    }

    /**
     * Log the error
     *
     * @param int $errno
     * @param string|null $errstr
     * @param string|null $errfile
     * @param int|null $errline
     * @param array|null $errcontext
     * @return void
     */
    private static function log($errno, $errstr = null, $errfile = null, $errline = null, array $errcontext = null)
    {
        $msg = "";

        switch ($errno) {
            case E_ERROR:
            case E_USER_ERROR:
                $msg .= "Fatal error:";
                break;
            case E_WARNING:
            case E_USER_WARNING:
                $msg .= "Warning:";
                break;
            case E_NOTICE:
            case E_USER_NOTICE:
                $msg .= "Notice:";
                break;
            default:
                $msg .= "Unknown:";
                break;
        }

        $msg .= " {$errstr} in {$errfile} on line {$errline}";

        Log::add($msg);
    }
}