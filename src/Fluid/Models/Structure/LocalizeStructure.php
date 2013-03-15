<?php

namespace Fluid\Models\Structure;

use Fluid\Fluid, Fluid\Database\Storage;

/**
 * Localize Site Structure
 *
 * @package fluid
 */
class LocalizeStructure {
	/**
	 * Get the default language
	 * 
	 * @return  string
	 */
	public static function getDefaultLanguage() {
		return Fluid::getConfig('languages')[0];
	}
	
	/**
	 * Localize file structure
	 * 
	 * @param   array   $structure
	 * @param   string  $language
	 * @return  void
	 */
	public static function localize($structure, $language) {				
		$localizedStructure = Storage::load("structure/structure_".(isset($language) ? $language : self::getDefaultLanguage()).".json");
		
		if ($language !== self::getDefaultLanguage()) {
			$defaultStructure = Storage::load("structure/structure_".self::getDefaultLanguage().".json");
		}
				
		$structure = self::removeUnlocalizedPages($structure, $language);
		
		$structure = self::addLocalizedNames($structure, $localizedStructure, (isset($defaultStructure) ? $defaultStructure : null));
				
		return $structure;
	}
	
	/**
	 * Remove pages that are not used localized
	 * 
	 * @param   array   $pages
	 * @param   string  $language
	 * @return  array
	 */
	private static function removeUnlocalizedPages($pages, $language) {
		$output = array();
		
		foreach($pages as $page) {
			if (isset($page->languages) && !in_array($language, $page->languages)) {
				continue;
			}
			
			if (isset($page->pages) && is_array($page->pages)) {
				$page->pages = self::removeUnlocalizedPages($page->pages, $language);
			}
			
			$output[] = $page;
		}
		
		return $output;
	}
	
	/**
	 * Add localized name to the pages or fallback to the default language name
	 * 
	 * @param   array   $pages
	 * @param   array   $localizedPages
	 * @param   array   $defaultPages
	 * @return  array
	 */
	public static function addLocalizedNames($pages, $localizedPages, $defaultPages = null) {
		$output = array();
		
		foreach ($pages as $page) {
			$page = clone $page;
			
			// Search page in localized pages
			foreach($localizedPages as $localizedPage) {
				if (isset($localizedPage->page) && $page->page === $localizedPage->page) {
					break;
				}
			}
			// Search page in default pages
			if (isset($defaultPages)) {
				foreach($defaultPages as $defaultPage) {
					if (isset($defaultPage->page) && $page->page === $defaultPage->page) {
						break;
					}
				}
			}
			
			if (!empty($localizedPage->name)) {
				$page->name = $localizedPage->name;
			} else if (isset($defaultPages) && !empty($defaultPage->name)) {
				$page->name = $defaultPage->name;
			} else {
				$page->name = $page->page;
			}
			
			if (isset($page->pages) && is_array($page->pages)) {
				$page->pages = self::addLocalizedNames($page->pages, $localizedPage, (isset($defaultPage) ? $defaultPage : null));
			}
			
			$output[] = $page;		
		}
		
		return $output;
	}
}