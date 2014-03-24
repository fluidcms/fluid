<?php

namespace Fluid\Debug;

use Fluid\Fluid;
use Fluid\Config;


/**
 * Class Log
 * @package Fluid\Debug
 * @deprecated
 */
class Log
{
    /**
     * Add text to the log
     *
     * @param string $text
     * @deprecated
     */
    public static function add($text)
    {
        /*if (Fluid::getDebugMode() === Fluid::DEBUG_LOG) {
            $file = Config::get('log');

            if (!empty($file)) {
                $content = file_get_contents($file);
                $content .= date('Y-m-d H:i:s') . " {$text}\n";
                file_put_contents($file, $content);
            }
        }*/
    }

    /**
     * Add a line to the log
     *
     * @deprecated
     */
    public static function line()
    {
        //self::add("==============================");
    }
}