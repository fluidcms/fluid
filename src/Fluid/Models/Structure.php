<?php

namespace Fluid\Models;

use Fluid\Fluid,
    Fluid\Database\Storage,
    Fluid\Models\Structure\LocalizeStructure,
    Exception;

/**
 * Site structure model
 *
 * @package fluid
 */
class Structure extends Storage
{
    protected static $dataFile = 'structure/structure_master.json';
    private $localized = array();

    public $pages;

    /**
     * Init
     *
     * @return  void
     */
    public function __construct()
    {
        $this->pages = self::getAll();
    }

    /**
     * Localize the structure
     *
     * @param   string  $language
     * @return  array
     */
    public function getLocalized($language = null)
    {
        if (null === $language) {
            $language = Fluid::getLanguage();
        }

        if (!isset($this->localized[$language])) {
            return $this->localized[$language] = LocalizeStructure::localize($this->pages, $language);
        } else {
            return $this->localized[$language];
        }
    }

    /**
     * Create a new page.
     *
     * @param   array   $attrs
     * @return  bool|string
     */
    public static function createPage($attrs)
    {
        try {
            Page::create($attrs['path'], $attrs['languages'], array());
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        $structure = Structure\Modify::addPage(new self, $attrs["path"], $attrs["index"], $attrs["page"], $attrs["url"], $attrs["layout"], $attrs["languages"]);
        $structure->store();

        return array(
            "id" => $attrs["path"],
            "page" => $attrs["page"],
            "url" => $attrs["url"],
            "layout" => $attrs["layout"],
            "languages" => $attrs["languages"]
        );
    }

    /**
     * Save structure.
     *
     * @return  void
     */
    public function store()
    {
        self::save(json_encode($this->pages, JSON_PRETTY_PRINT), self::$dataFile);
    }
}