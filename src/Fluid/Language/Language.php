<?php

namespace Fluid\Language;

use Fluid;
use Fluid\Config;
use Exception;

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
     * @return array
     */
    public static function getLanguages()
    {
        $output = array();
        foreach (Config::get('languages') as $language) {
            $output[] = array(
                'language' => $language
            );
        }
        return $output;
    }

    /**
     * Validate language
     *
     * @param string $value
     * @throws Exception
     * @return bool
     */
    public static function validateLanguage($value)
    {
        if (!is_string($value)) {
            throw new Exception("Invalid language");
        }

        $valid = self::getLanguages();
        $found = false;
        foreach ($valid as $haystack) {
            if ($haystack['language'] === $value) {
                $found = true;
                break;
            }
        }

        if (!$found) {
            throw new Exception("Invalid language");
        }

        return true;
    }

    /**
     * Validate languages
     *
     * @param array $value
     * @throws Exception
     * @return bool
     */
    public static function validateLanguages(array $value)
    {
        if (!is_array($value)) {
            throw new Exception("Invalid languages");
        }

        $valid = self::getLanguages();
        foreach ($value as $needle) {
            $found = false;
            foreach ($valid as $haystack) {
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