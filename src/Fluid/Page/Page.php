<?php

namespace Fluid\Page;

use Exception,
    Fluid\Fluid,
    Fluid\Storage\FileSystem;

/**
 * Page model
 *
 * @package fluid
 */
class Page extends FileSystem
{
    public $id;
    public $data;
    public $variables;

    /**
     * Init
     *
     * @param   string  $id
     */
    public function __construct($id = null)
    {
        if (null !== $id) {
            $this->id = $id;
        }
    }

    /**
     * Get a page
     *
     * @param   string  $id
     * @return  self
     */
    public static function get($id = null)
    {
        return new self($id);
    }

    /**
     * Get the page data
     *
     * @return  array
     */
    public function getData()
    {
        try {
            if (!empty($this->id)) {
                $file = 'pages/' . $this->id . '_' . Fluid::getLanguage() . '.json';
            } else {
                $file = 'base_' . Fluid::getLanguage() . '.json';
            }
            $data = self::load($file);
            return $data;
        } catch (Exception $e) {
            null;
        }
        return array();
    }

    /**
     * Check if page has parent page
     *
     * @return  bool
     */
#    public function hasParent()
#    {
#        return (isset($this->parent) ? true : false);
#    }

    /**
     * Update a page
     *
     * @param   string  $page
     * @param   string  $request
     * @return  bool
     */
    public static function update($page, $request)
    {
        $file = 'pages/' . $page . '_' . $request['language'] . '.json';

        unset($request['data']['pages']); // TODO first, don't send these, then find a better way to sanitize inputs
        unset($request['data']['url']); //  !! SAME
        unset($request['data']['page']); // !! SAME
        unset($request['data']['layout']); //  !! SAME
        unset($request['data']['languages']); // !! SAME

        self::save(json_encode($request['data'], JSON_PRETTY_PRINT), $file);

        return true;
    }

    /**
     * Create a page
     *
     * @param   string      $page
     * @param   array       $languages
     * @param   array       $content
     * @throws  Exception
     * @return  void
     */
    public static function create($page, $languages, $content = array())
    {
        if (!is_array($languages)) {
            $languages = array($languages);
        }

        try {
            Language::validateLanguages($languages);
            $path = explode('/', $page);
            array_walk($path, array("Fluid\\Models\\Page\\Validator", "name"));
            Page\Validator::content($content);
        } catch (Exception $e) {
            throw new Exception("Cannot create page: " . $e->getMessage());
        }

        $page = trim($page, "/");

        foreach ($languages as $language) {
            $file = 'pages/' . $page . '_' . $language . '.json';
            self::save(json_encode($content, JSON_PRETTY_PRINT), $file);
        }
    }

    /**
     * Delete a page
     *
     * @param   string      $page
     * @throws  Exception
     * @return  bool
     */
    public static function delete($page)
    {
        $page = trim(str_replace('..', '', $page), '/.');
        $dir = Fluid::getBranchStorage() . "pages/" . dirname($page);
        $page = basename($page);
        foreach (scandir($dir) as $file) {
            if (preg_match("/^{$page}_[a-z]{2,2}\\-[A-Z]{2,2}\\.json$/", $file)) {
                unlink($dir."/".$file);
            }
        }

        return true;
    }

    /**
     * Rename a page
     *
     * @param   string      $oldId
     * @param   string      $newId
     * @throws  Exception
     * @return  bool
     */
    public static function rename($oldId, $newId)
    {
        $dir = trim(str_replace('..', '', $oldId), '/.');

        $dir = Fluid::getBranchStorage() . "pages/" . trim(dirname($dir), '.');

        $oldName = basename($oldId);
        $newName = basename($newId);

        $found = false;

        foreach (scandir($dir) as $file) {
            if (preg_match("/^{$oldName}(_[a-z]{2,2}\\-[A-Z]{2,2})\\.json$/", $file, $match)) {
                rename(
                    $dir."/".$file,
                    $dir."/".$newName . $match[1] . ".json"
                );
                $found = true;
            }
        }

        if (!$found) {
            throw new Exception("The page does not exists");
        }

        return true;
    }

    /**
     * Move a page
     *
     * @param   string      $to
     * @throws  Exception
     * @return  bool
     */
    public function move($to)
    {
        $to = trim(str_replace('..', '', $to), '/.');

        $fromDir = Fluid::getBranchStorage() . "pages/" . trim(dirname($this->id), '.');
        $toDir = Fluid::getBranchStorage() . "pages/" . trim($to, '.');

        if (!is_dir($toDir)) {
            mkdir($toDir, 0777, true);
        }

        $page = basename($this->id);

        if (is_dir($fromDir)) {
            foreach (scandir($fromDir) as $file) {
                if (preg_match("/^{$page}_[a-z]{2,2}\\-[A-Z]{2,2}\\.json$/", $file)) {
                    if (!is_dir($toDir)) {
                        mkdir($toDir);
                    }
                    rename(
                        $fromDir."/".$file,
                        $toDir."/".$file
                    );
                }
            }
        }

        return true;
    }

    /**
     * Merge template data with the page data
     *
     * @param   string  $content    The page content with the template data
     * @return  array
     */
#    public static function mergeTemplateData($content)
#    {
#        list($language, $page, $variables, $data) = Page\MergeTemplateData::getTemplateData($content);
#        Fluid::setLanguage($language);
#
#        $site = new Site();
#        $structure = new Structure();
#        $page = new Page($structure, $page);
#
#        Page\MergeTemplateData::merge($site, $page, $variables, $data);
#
#        return array(
#            'page' => $page,
#            'site' => $site,
#            'structure' => $structure
#        );
#    }
}