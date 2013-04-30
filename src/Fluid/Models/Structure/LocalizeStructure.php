<?php

namespace Fluid\Models\Structure;

use Fluid\Fluid, Fluid\Database\Storage;

/**
 * Localize Site Structure
 *
 * @package fluid
 */
class LocalizeStructure
{
    /**
     * Get the default language
     *
     * @return  string
     */
    public static function getDefaultLanguage()
    {
        return Fluid::getConfig('languages')[0];
    }

    /**
     * Localize the site structure.
     *
     * @param   array   $structure  The master structure
     * @param   string  $language   The language to localize the structure to
     * @param   string  $parent     The current page's parent
     * @return  array   The localized structure
     */
    public static function localize($structure, $language, $parent = '')
    {
        $output = array();
        foreach ($structure as $page) {
            if (in_array($language, $page['languages'])) {
                $name = self::getLocalizedName($parent, $page['page'], $language);
                if (isset($page['pages']) && is_array($page['pages'])) {
                    $newParent = trim($parent . "/" . $page['page'], '/');
                    $pages = self::localize($page['pages'], $language, $newParent);
                    $output[] = array_merge($page, array(
                        "name" => $name,
                        "pages" => $pages
                    ));
                } else {
                    $output[] = array_merge($page, array("name" => $name));
                }
            }
        }

        return $output;
    }

    /**
     * Remove pages that are not used localized
     *
     * @param   string  $parent     The current page's parent
     * @param   string  $page       The current page
     * @param   string  $language   The language to localize the structure to
     * @return  array
     */
    private static function getLocalizedName($parent, $page, $language)
    {
        if (!empty($parent)) {
            $parent .= '/';
        }

        $page = Storage::load('pages/' . $parent . $page . '_' . $language . ".json");

        return $page['name'];
    }
}