<?php

namespace Fluid\Models;

use Exception, Fluid\Fluid, Fluid\Database\Storage;

/**
 * Page model
 *
 * @package fluid
 */
class Page extends Storage {
	public $page;
	public $parent;
	public $data;
	public $variables;
		
	/**
	 * Init
	 * 
	 * @param   Structure   $structure  The site's structure
	 * @param   string      $name       The unique identifier of a page (i.e. contact/form)
	 * @return  void
	 */
	public function __construct(Structure $structure, $page) {
		$this->page = $page;
		
		// Check if page has parents
		$parent = explode('/', strrev($page), 2);
		if (isset($parent[1])) {
			$parent = strrev($parent[1]);
			$this->parent = new Page($structure, $parent);
		}
		
		// Load page data
		try {
			$this->data = self::load('pages/'.$page.'_'.Fluid::getLanguage().'.json');
		} catch (Exception $e) {
			null;
		}
	}
		
	/**
	 * Check if page has parent page
	 * 
	 * @return  bool
	 */
	public function hasParent() {
		return (isset($this->parent) ? true : false);
	}
		
	/**
	 * Merge template data with the page data
	 * 
	 * @param   string  $content    The page content with the template data
	 * @return  array
	 */
	public static function mergeTemplateData($content) {		
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