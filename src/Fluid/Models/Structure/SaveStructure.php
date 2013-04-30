<?php

namespace Fluid\Models\Structure;

use Fluid\Fluid, Fluid\Models\Structure, Fluid\Database\Storage;

/**
 * Save Site Structure
 *
 * @package fluid
 */
class SaveStructure
{
    private static $dir = '';
    private static $localizedChanges = array();
    private static $localizedAdds = array();
    private static $localizedStructures = array();
    private static $newLocalizedStructures = array();
    private static $structure = array();

    /**
     * Save a new structure
     *
     * @param   array       $newStructure
     * @param   string      $dataFile
     * @return    void
     */
    public static function save($newStructure, $dataFile)
    {
        self::$dir = dirname($dataFile) . "/";
        self::getLocalizedStructures();

        foreach (Fluid::getConfig('languages') as $language) {
            self::$newLocalizedStructures[$language] = array();
        }

        $structure = self::loopStructure($newStructure);

        self::applyLocalizedChanges();

        Storage::save(json_encode($structure, JSON_PRETTY_PRINT), $dataFile);

        foreach (Fluid::getConfig('languages') as $language) {
            Storage::save(json_encode(self::$newLocalizedStructures[$language], JSON_PRETTY_PRINT), self::$dir . "structure_{$language}.json");
        }
    }

    /**
     * Get all localized versions of the structure
     *
     * @return  void
     */
    public static function getLocalizedStructures()
    {
        foreach (Fluid::getConfig('languages') as $language) {
            self::$localizedStructures[$language] = Storage::load(self::$dir . "structure_{$language}.json");
        }
    }

    /**
     * Loop through new structure and save it
     *
     * @param   array   $structure
     * @param   string  $parent
     * @return  array
     */
    public static function loopStructure($structure, $parent = '')
    {
        $output = array();
        $count = 0;

        foreach ($structure as $item) {
            $id = trim($parent . '/' . $item['page'], '/');

            self::changeLocalizedStructure($item['id'], $id, $count, $item['page']);

            if (isset($item['pages']) && count($item['pages'])) {
                $item['pages'] = self::loopStructure($item['pages'], $id);
            }

            unset($item['id']);
            $output[] = $item;
            $count++;
        }
        return $output;
    }

    /**
     * Update an item in the localized structure
     *
     * @param   string  $id
     * @param   string  $newId
     * @param   int     $newPos
     * @param   string  $page
     * @return  void
     */
    public static function changeLocalizedStructure($id, $newId, $newPos, $page)
    {
        $level = count(explode('/', $id));
        self::$localizedChanges[] = array($level, $id, $newId, $newPos, $page);
    }

    /**
     * Apply changes to localized structure
     *
     * @return  void
     */
    public static function applyLocalizedChanges()
    {
        array_multisort(self::$localizedChanges, SORT_DESC);

        foreach (self::$localizedChanges as $change) {
            list($level, $id, $newId, $newPos, $page) = $change;
            foreach (self::$localizedStructures as $language => $localizedStructure) {
                $item = self::getLocalizedItem($language, $id);
                if (null === $item) {
                    $item = array('page' => $page, "name" => "");
                }
                $level = count(explode('/', $newId));
                self::$localizedAdds[] = array($level, $language, $item, $newId, $newPos);
            }
        }

        array_multisort(self::$localizedAdds);
        foreach (self::$localizedAdds as $add) {
            list($level, $language, $item, $newId, $newPos) = $add;
            self::addLocalizedItem($language, $item, $newId, $newPos);
        }
    }

    /**
     * Get an item from localized structure
     *
     * @param   string  $language
     * @param   string  $id
     * @return    array
     */
    public static function getLocalizedItem($language, $id)
    {
        $path = explode('/', $id);
        $item = self::$localizedStructures[$language];

        $arrayKey = '';
        $count = 0;
        do {
            $found = false;
            foreach ($item as $key => $value) {
                if (isset($value['page']) && $value['page'] === $path[$count]) {
                    if (isset($value['pages']) && count($value['pages'])) {
                        $item = $item[$key]['pages'];
                        $arrayKey .= "[{$key}]['pages']";
                    } else {
                        $item = $item[$key];
                        $arrayKey .= "[{$key}]";
                    }
                    $found = true;
                    $count++;
                    break;
                }
            }
            if (!$found) {
                $item = null;
                $count = -1;
                unset($arrayKey);
            }
        } while (isset($path[$count]));

        if (!empty($arrayKey)) {
            eval('unset(self::$localizedStructures["' . $language . '"]' . $arrayKey . ');');
        }

        return $item;
    }

    /**
     * Add item to localized structure
     *
     * @param   string  $language
     * @param   array   $item
     * @param   string  $id
     * @param   int     $pos
     * @return  void
     */
    public static function addLocalizedItem($language, $item, $id, $pos)
    {
        $path = explode('/', $id);
        $name = end($path);
        $path = array_slice($path, 0, -1);
        $parent = self::$newLocalizedStructures[$language];

        $arrayKey = '';
        $count = 0;
        while (isset($path[$count])) {
            foreach ($parent as $key => $value) {
                if ($value['page'] === $path[$count]) {
                    $parent = isset($parent[$key]['pages']) ? $parent[$key]['pages'] : array();
                    $arrayKey .= "[{$key}]['pages']";
                    $count++;
                    break;
                }
            }
        }

        $item['page'] = $name;

        $parent = array_merge(
            array_slice($parent, 0, $pos + 1),
            array($item),
            array_slice($parent, $pos + 1)
        );

        eval('self::$newLocalizedStructures["' . $language . '"]' . $arrayKey . ' = $parent;');
    }
}