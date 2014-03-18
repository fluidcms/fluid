<?php
namespace Fluid\Page;

use Exception;
use Fluid\Fluid;
use Fluid\Config;
use Fluid\Layout\Layout;
use Fluid\Variable\VariableRepository;
use Fluid\StorageInterface;
use Fluid\XmlMappingLoaderInterface;

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
     * @var PageRepository
     */
    private $pages;

    /**
     * @var VariableRepository
     */
    private $variables;

    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var XmlMappingLoaderInterface
     */
    private $xmlMappingLoader;

    /**
     * @var PageMapper
     */
    private $pageMapper;

    /**
     * @var PageConfig
     */
    private $config;

    /**
     * @param StorageInterface $storage
     * @param XmlMappingLoaderInterface $xmlMappingLoader
     * @param PageMapper $pageMapper
     */
    public function __construct(StorageInterface $storage, XmlMappingLoaderInterface $xmlMappingLoader, PageMapper $pageMapper)
    {
        $this->setStorage($storage);
        $this->setXmlMappingLoader($xmlMappingLoader);
        $this->setPageMapper($pageMapper);
        $this->setConfig(new PageConfig($this));
        $this->setPages(new PageRepository($storage, $xmlMappingLoader, $pageMapper));
        $this->setVariables(new VariableRepository);
    }

    /**
     * @param array|string $attributes
     * @param mixed|null $value
     */
    public function set($attributes, $value = null)
    {
        if (is_string($attributes)) {
            $attributes = [$attributes => $value];
        }

        foreach ($attributes as $key => $value) {
            if ($key === 'name') {
                $this->setName($value);
            } elseif ($key === 'pages') {
                $this->getPages()->addPages($value);
            } elseif ($key === 'variables') {
                $this->getVariables()->addVariables($value);
            }
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
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->getPages()->setPath($name);
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
     * @param VariableRepository $variables
     * @return $this
     */
    public function setVariables(VariableRepository $variables)
    {
        $this->variables = $variables;
        return $this;
    }

    /**
     * @return VariableRepository
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * @param PageMapper $pageMapper
     * @return $this
     */
    public function setPageMapper(PageMapper $pageMapper)
    {
        $this->pageMapper = $pageMapper;
        return $this;
    }

    /**
     * @return PageMapper
     */
    public function getPageMapper()
    {
        return $this->pageMapper;
    }

    /**
     * @param StorageInterface $storage
     * @return $this
     */
    public function setStorage(StorageInterface $storage)
    {
        $this->storage = $storage;
        return $this;
    }

    /**
     * @return StorageInterface
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @param XmlMappingLoaderInterface $xmlMappingLoader
     * @return $this
     */
    public function setXmlMappingLoader(XmlMappingLoaderInterface $xmlMappingLoader)
    {
        $this->xmlMappingLoader = $xmlMappingLoader;
        return $this;
    }

    /**
     * @return XmlMappingLoaderInterface
     */
    public function getXmlMappingLoader()
    {
        return $this->xmlMappingLoader;
    }

    /**
     * @param PageConfig $config
     * @return $this
     */
    public function setConfig(PageConfig $config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @return PageConfig
     */
    public function getConfig()
    {
        return $this->config;
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