<?php

namespace Fluid\Language;

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
        $output = array();
        foreach(Fluid\Fluid::getConfig('languages') as $language) {
            $output[] = array(
                'language' => $language
            );
        }
        return $output;
    }

    /**
     * Validate languages
     *
     * @param   array       $value
     * @throws  Exception
     * @return  bool
     */
    public static function validateLanguages($value)
    {
        if (!is_array($value)) {
            throw new Exception("Invalid languages");
        }

        $valid = self::getLanguages();
        foreach($value as $needle) {
            $found = false;
            foreach($valid as $haystack) {
                if ($haystack['language'] === $needle) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                throw new Exception("Invalid languages");
            }
        }
        return true;
    }
}