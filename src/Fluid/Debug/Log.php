<?php

namespace Fluid\Debug;
use Fluid\Fluid;

class Log
{
    /**
     * Add a line to the log
     *
     * @param   string  $line
     * @return  void
     */
    public static function add($line)
    {
        if (Fluid::getDebugMode() === Fluid::DEBUG_LOG) {
            $file = Fluid::getConfig('log');

            if (!empty($file)) {
                $content = file_get_contents($file);
                $content .= date('Y-m-d H:i:s') . " {$line}\n";
                file_put_contents($file, $content);
            }
        }
    }
}