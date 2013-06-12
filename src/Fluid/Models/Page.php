<?php

namespace Fluid\Models;

use Exception, Fluid\Fluid, Fluid\Database\Storage;

/**
 * Page model
 *
 * @package fluid
 */
class Page extends Storage
{
    public $page;
    public $pages;
    public $parent;
    public $data;
    public $variables;

    /**
     * Init
     *
     * @param   Structure   $structure  The site's structure
     * @param   string      $page       The unique identifier of a page (i.e. contact/form)
     * @return  void
     */
    public function __construct(Structure $structure, $page)
    {
        $this->page = $page;

        // Check if page has parents
        $parent = explode('/', strrev($page), 2);
        if (isset($parent[1])) {
            $parent = strrev($parent[1]);
            $this->parent = new Page($structure, $parent);
        }

        // TODO this is HEAVY on the system, to optimize
        // IDEA remove the data from the Page constructor, move it to a method (getData)
        // TMPFIX we search the localized structure to AT LEAST get the pages names
        $structurePage = $structure->findLocalizedPage($page);
        if (isset($structurePage['pages']) && is_array($structurePage['pages'])) {
            // TODO not working, too much looping. Ideally, we need to be able to access all
            // the data of the site from twig. So what we need is a big data array containing
            // everything. Of course this will need to be cached.
//            foreach($structurePage['pages'] as $item) {
//                if (is_string($parent)) {
//                    $id = $parent . "/" . $page . "/" . $item['page'];
//                } else {
//                    $id = $page . "/" . $item['page'];
//                }
//                $this->pages[] = new Page($structure, $id);
//            }
            $this->pages = $structurePage['pages'];
        }

        // Load page data
        // TODO move to a getter
        try {
            $this->data = array_merge(
                [
                    'page' => $this->page,
                    'url' => $structurePage['url'],
                    'layout' => $structurePage['layout'],
                    'languages' => $structurePage['languages'],
                    'pages' => $this->pages
                ],
                self::load('pages/' . $page . '_' . Fluid::getLanguage() . '.json')
            );
        } catch (Exception $e) {
            null;
        }
    }

    /**
     * Check if page has parent page
     *
     * @return  bool
     */
    public function hasParent()
    {
        return (isset($this->parent) ? true : false);
    }

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
     * @param   string      $from
     * @param   string      $to
     * @throws  Exception
     * @return  bool
     */
    public static function move($from, $to)
    {
        $from = trim(str_replace('..', '', $from), '/.');
        $to = trim(str_replace('..', '', $to), '/.');

        $fromDir = Fluid::getBranchStorage() . "pages/" . trim(dirname($from), '.');
        $toDir = Fluid::getBranchStorage() . "pages/" . trim($to, '.');

        $page = basename($from);

        $found = false;

        foreach (scandir($fromDir) as $file) {
            if (preg_match("/^{$page}_[a-z]{2,2}\\-[A-Z]{2,2}\\.json$/", $file)) {
                if (!is_dir($toDir)) {
                    mkdir($toDir);
                }
                rename(
                    $fromDir."/".$file,
                    $toDir."/".$file
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
     * Merge template data with the page data
     *
     * @param   string  $content    The page content with the template data
     * @return  array
     */
    public static function mergeTemplateData($content)
    {
        list($language, $page, $variables, $data) = Page\MergeTemplateData::getTemplateData($content);
        Fluid::setLanguage($language);

        $site = new Site();
        $structure = new Structure();
        $page = new Page($structure, $page);

        Page\MergeTemplateData::merge($site, $page, $variables, $data);

        return array(
            'page' => $page,
            'site' => $site,
            'structure' => $structure
        );
    }
}