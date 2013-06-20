<?php

namespace Fluid\Models;

use Fluid, Exception;

/**
 * Language model
 *
 * @package fluid
 */
class Language
{
    /**
     * Get languages
     *
     * @return  array
     */
    public static function getLanguages()
    {
        return Fluid\Fluid::getConfig('languages');
    }

    /**
     * Validate languages
     *
     * @param   array       $input
     * @throws  Exception   Invalid language
     * @return  bool
     */
    public static function validateLanguages($input)
    {
        $valid = self::getLanguages();
        foreach($input as $language) {
            if (!in_array($language, $valid)) {
                throw new Exception("Invalid language.");
            }
        }
        return true;
    }
}