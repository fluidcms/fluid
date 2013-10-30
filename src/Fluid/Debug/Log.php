<?php

namespace Fluid\Debug;
use Fluid\Fluid;

class Log
{
    /**
     * Add text to the log
     *
     * @param string $text
     */
    public static function add($text)
    {
        if (Fluid::getDebugMode() === Fluid::DEBUG_LOG) {
            $file = Fluid::getConfig('log');

            if (!empty($file)) {
                $content = file_get_contents($file);
                $content .= date('Y-m-d H:i:s') . " {$text}\n";
                file_put_contents($file, $content);
            }
        }
    }

    /**
     * Add a line to the log
     */
    public static function line()
    {
        self::add("==============================");
    }
}