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
    private $id;
    private $page;
    private $language;
    private $languages;
    private $layout;
    private $url;
    private $pages;

    /**
     * Init
     *
     * @param   string  $id
     * @param   string  $page
     * @param   array   $languages
     * @param   string  $layout
     * @param   string  $url
     * @param   array   $pages
     */
    public function __construct($id = null, $page = null, $languages = null, $layout = null, $url = null, $pages = null)
    {
        $this->id = $id;
        $this->page = $page;
        $this->languages = $languages;
        $this->layout = $layout;
        $this->url = $url;
        $this->pages = $pages;
    }

    /**
     * Get a page
     *
     * @param   mixed  $id
     * @param   string  $language
     * @return  self
     */
    public static function get($id = null, $language = null)
    {
        if (is_array($id)) {
            $obj = new self($id['id'], $id['page'], $id['languages'], $id['layout'], $id['url'], (isset($id['pages']) ? $id['pages'] : null));
        } else {
            $obj = new self($id);
        }

        if (null !== $language) {
            $obj->setLanguage($language);
        }

        return $obj;
    }

    /**
     * Set the language to get the data
     *
     * @param   string  $value
     * @throws  Exception
     * @return  void
     */
    public function setLanguage($value)
    {
        $languages = Fluid::getConfig("languages");
        if (in_array($value, $languages)) {
            $this->language = $value;
            return;
        }

        throw new Exception("Language is not valid");
    }

    /**
     * Get the processed page data
     *
     * @return  array
     */
    public function getData()
    {
        if (empty($this->layout)) {
            $this->layout = 'global';
        }

        return ParseData::parse($this, Layout::get($this->layout));
    }

    /**
     * Get the raw page data
     *
     * @return  array
     */
    public function getRawData()
    {
        $id = $this->getId();
        $language = empty($this->language) ? Fluid::getLanguage() : $this->language;

        if (!empty($id)) {
            $file = 'pages/' . $id . '_' . $language . '.json';
        } else {
            $file = 'global_' . $language . '.json';
        }

        // TODO: variables in the definition that have not been saved will not appear here, variables that have been deleted will stil appear here
        // TODO: this is not a big problem when using templates engines that will not report an error when using unset variables but will become
        // TODO: a big problem when using the variables in a PHP template. Like the getData method, this method should parse the variables with the
        // TODO: definition to make sure we return all existing variables even those that have not been saved yet.
        return self::load($file);
    }

    /**
     * Get the page id
     *
     * @return  string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the page layout
     *
     * @return  string
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * Update a page's data
     *
     * @param   array   $data
     * @return  bool
     */
    public function update($data)
    {
        $id = $this->getId();

        if (!empty($id)) {
            $file = 'pages/' . $id . '_' . $this->language . '.json';
        } else {
            $file = 'global_' . $this->language . '.json';
        }

        self::save(json_encode($data, JSON_PRETTY_PRINT), $file);
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
}