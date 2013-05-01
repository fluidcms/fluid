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
     * @throws  Exception
     * @return  array
     */
    public static function createPage($attrs)
    {
        Page::create($attrs['path'], $attrs['languages'], array());
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
     * Create a new page.
     *
     * @param   string   $id
     * @return  bool
     */
    public static function deletePage($id)
    {
        Page::delete($id);
        $structure = Structure\Modify::deletePage(new self, $id);
        $structure->store();

        return true;
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