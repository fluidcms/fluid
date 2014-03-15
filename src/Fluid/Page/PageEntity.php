<?php
namespace Fluid\Page;

use Exception;
use Fluid\Fluid;
use Fluid\Config;
use Fluid\Layout\Layout;
use Fluid\Variable\VariableRepository;

/**
 * Page Entity
 *
 * @package fluid
 */
class PageEntity
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $languages = [];

    /**
     * @var string
     */
    private $layout;

    /**
     * @var string
     */
    private $url;

    /**
     * @var PageRepository
     */
    private $pages;

    /**
     * @var VariableRepository
     */
    private $variables;

    /**
     * Init
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        if (isset($attributes['id'])) {
            $this->setId($attributes['id']);
        }
        if (isset($attributes['name'])) {
            $this->setName($attributes['name']);
        }
        if (isset($attributes['languages'])) {
            $this->setLanguages($attributes['languages']);
        }
        if (isset($attributes['layout'])) {
            $this->setLayout($attributes['layout']);
        }
        if (isset($attributes['url'])) {
            $this->setUrl($attributes['url']);
        }
        if (isset($attributes['pages'])) {
            $this->setPages(new PageRepository($attributes['pages']));
        } else {
            $this->setPages(new PageRepository);
        }
        if (isset($attributes['variables'])) {
            $this->setVariables(new VariableRepository($attributes['variables']));
        } else {
            $this->setVariables(new VariableRepository);
        }
    }

    /**
     * @param string $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param array $languages
     * @return $this
     */
    public function setLanguages($languages)
    {
        $this->languages = $languages;
        return $this;
    }

    /**
     * @return array
     */
    public function getLanguages()
    {
        return $this->languages;
    }

    /**
     * @param string $layout
     * @return $this
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;
        return $this;
    }

    /**
     * @return string
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param PageRepository $pages
     * @return $this
     */
    public function setPages(PageRepository $pages)
    {
        $this->pages = $pages;
        return $this;
    }

    /**
     * @return PageRepository
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param VariableRepository $variables
     * @return $this
     */
    public function setVariables(VariableRepository $variables)
    {
        $this->variables = $variables;
        return $this;
    }

    /**
     * @return \Fluid\Variable\VariableRepository
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////

    /**
     * Get a page
     *
     * @param mixed $id
     * @param string $language
     * @return self
     */
    public static function ____get($id = null, $language = null)
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
     * Get a variable
     *
     * @param string $item
     * @param string $group
     * @return array
     */
    public function ___getVariable($item, $group = null)
    {
        $data = $this->getRawData();
        if (null !== $group) {
            return $data[$group][$item];
        } else {
            return $data[$item];
        }
    }

    /**
     * Set the current page language
     *
     * @param string $value
     * @throws Exception
     */
    public function ___setLanguage($value)
    {
        $languages = Config::get("languages");
        if (in_array($value, $languages)) {
            $this->language = $value;
            return;
        }

        throw new Exception("Language is not valid");
    }

    /**
     * Get the current page language
     *
     * @return string
     */
    public function ___getLanguage()
    {
        return $this->language;
    }

    /**
     * Get the processed page data
     *
     * @param string $language
     * @return array
     */
    public function ___getData($language = null)
    {
        if (empty($this->layout)) {
            $this->layout = 'global';
        }
        try {
            return ParseData::parse($this, Layout::get($this->layout), $language);
        } catch (Exception $e) {
            null;
        }
        return array();
    }

    /**
     * Get the raw page data
     *
     * @param string $language
     * @return array
     */
    public function ___getRawData($language = null)
    {
        $id = $this->getId();
        if (null === $language) {
            $language = empty($this->language) ? Fluid::getLanguage() : $this->language;
        }

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
     * Update a page's data
     *
     * @param array $data
     * @throws Exception
     * @return bool
     */
    public function ____update(array $data)
    {
        UpdateData::update($this, $data);
    }

    /**
     * Create a page
     *
     * @param string $page
     * @param string $parent
     * @param array $languages
     * @param string $layout
     * @param string $url
     * @throws Exception
     * @return array
     */
    public static function ____create($page, $parent, array $languages, $layout, $url)
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
     * @throws Exception
     * @return bool
     */
    public function _____delete()
    {
        $deletePage = function ($path, $name = false) use (&$deletePage) {
            foreach (scandir($path) as $file) {
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
            Fluid::getBranchStorage() . "/pages/" . dirname($this->id),
            basename($this->id)
        );

        return true;
    }

    /**
     * Edit a page's configuration
     *
     * @param string $id
     * @param string $page
     * @param array $languages
     * @param string $layout
     * @param string $url
     * @throws Exception
     * @return bool
     */
    public static function _____config($id, $page, array $languages, $layout, $url)
    {
        Validator::pageValidator($page, $languages, $layout, $url);

        $oldName = basename($id);

        $dir = preg_replace('!/\.*/!', '/', dirname($id));
        $dir = Fluid::getBranchStorage() . "/pages/" . trim($dir, '/ ');

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
                    } // Rename file
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
        } // Move files
        else {
            throw new Exception('Unknown directory');
        }

        // Create new language files
        foreach ($languages as $language) {
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
     * @param string $to
     * @throws Exception
     * @return bool
     */
    public function _____move($to)
    {
        $to = trim(str_replace('..', '', $to), '/.');

        $fromDir = Fluid::getBranchStorage() . "/pages/" . trim(dirname($this->id), '.');
        $toDir = Fluid::getBranchStorage() . "/pages/" . trim($to, '.');

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
                        $fromDir . "/" . $file,
                        $toDir . "/" . $file
                    );
                }
            }
        }

        return true;
    }
}