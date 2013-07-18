<?php

namespace Fluid\Page;

use Exception,
    Fluid\Fluid,
    Fluid\Language\Language,
    Fluid\Layout\Layout,
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
     * @param   string  $page
     * @param   string  $parent
     * @param   array   $languages
     * @param   string  $layout
     * @param   string  $url
     * @throws  Exception
     * @return  array
     */
    public static function create($page, $parent, $languages, $layout, $url)
    {
        Validator::newPageValidator($page, $parent, $languages, $layout, $url);

        $id = trim($parent . "/" . $page, "/");

        foreach ($languages as $language) {
            $file = 'pages/' . $id . '_' . $language . '.json';
            self::save(json_encode(array(), JSON_PRETTY_PRINT), $file);
        }

        return array(
            'id' => $id,
            'page' => $page,
            'languages' => $languages,
            'layout' => $layout,
            'url' => $url
        );
    }

    /**
     * Delete a page
     *
     * @throws  Exception
     * @return  bool
     */
    public function delete()
    {
        $deletePage = function($path, $name = false) use (&$deletePage) {
            foreach(scandir($path) as $file) {
                $link = $path . "/" . $file;
                if ($file == '.' || $file == '..') {
                    continue;
                } else if (
                    ($name === false && is_file($link)) ||
                    ($name && (preg_match("/^{$name}_[a-z]{2,2}\\-[A-Z]{2,2}\\.json$/", $file)))
                ) {
                    unlink($link);
                } else if (
                    ($name === false && is_dir($link)) ||
                    ($name && $file === $name)
                ) {
                    $deletePage($link);
                    rmdir($link);
                }
            }
        };

        $deletePage(
            Fluid::getBranchStorage() . "pages/" . dirname($this->id),
            basename($this->id)
        );

        return true;
    }

    /**
     * Edit a page's configuration
     *
     * @param   string  $id
     * @param   string  $page
     * @param   array   $languages
     * @param   string  $layout
     * @param   string  $url
     * @throws  Exception
     * @return  bool
     */
    public static function config($id, $page, $languages, $layout, $url)
    {
        Validator::pageValidator($page, $languages, $layout, $url);

        $oldName = basename($id);

        $dir = preg_replace('!/\.*/!', '/', dirname($id));
        $dir = Fluid::getBranchStorage() . "pages/" . trim($dir, '/ ');

        $existingFiles = array();
        if (is_dir($dir)) {
            foreach (scandir($dir) as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                } else if (preg_match("/^{$oldName}(_[a-z]{2,2}\\-[A-Z]{2,2})\\.json$/", $file, $match)) {
                    // Delete files for removed languages
                    $language = substr($match[1], 1);
                    if (!in_array($language, $languages)) {
                        unlink("{$dir}/{$file}");
                    }

                    // Rename file
                    else {
                        $existingFiles[] = $language;
                        rename(
                            "{$dir}/{$file}",
                            "{$dir}/{$page}_{$language}.json"
                        );
                    }
                } else if ($file === $oldName && is_dir("{$dir}/{$file}")) {
                    rename(
                        "{$dir}/{$file}",
                        "{$dir}/{$page}"
                    );
                }
            }

            // Move files
        } else {
            throw new Exception('Unknown directory');
        }

        // Create new language files
        foreach($languages as $language) {
            if (!in_array($language, $existingFiles)) {
                file_put_contents(
                    "{$dir}/{$page}_{$language}.json",
                    json_encode(array(), JSON_PRETTY_PRINT)
                );
            }
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