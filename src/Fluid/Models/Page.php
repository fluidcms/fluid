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

        // Load page data
        try {
            $this->data = self::load('pages/' . $page . '_' . Fluid::getLanguage() . '.json');
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
     * @return  void
     */
    public static function update($page, $request)
    {
        $request = json_decode($request, true);
        $file = 'pages/' . $page . '_' . $request['language'] . '.json';

        self::save(json_encode($request['data'], JSON_PRETTY_PRINT), $file);
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
        $page = str_replace(array('..', '//'), '', $page);
        $dir = Fluid::getConfig('storage') . "pages/" . dirname($page);
        $page = basename($page);
        foreach (scandir($dir) as $file) {
            if (preg_match("/^{$page}_[a-z]{2,2}\\-[A-Z]{2,2}\\.json$/", $file)) {
                unlink($dir."/".$file);
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