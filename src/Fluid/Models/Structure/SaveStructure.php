<?php

namespace Fluid\Models\Structure;

use Fluid\Fluid, Fluid\Models\Structure, Fluid\Database\Storage;

/**
 * Save Site Structure
 *
 * @package fluid
 */
class SaveStructure {
	private static $dir = '';
	private static $localizedChanges = array();
	private static $localizedStructures = array();
	private static $structure = array();
	
	/**
	 * Save a new structure
	 * 
	 * @param   array       $newStructure
	 * @param   string      $dataFile
	 * @return	void
	 */
	public static function save($newStructure, $dataFile) {
		self::$dir = dirname($dataFile) . "/";
		self::getLocalizedStructures();
		
		$structure = self::loopStructure($newStructure);
		
		self::applyLocalizedChanges();
		
		Storage::save(json_encode($structure), $dataFile);
		
		foreach(Fluid::getConfig('languages') as $language) {
			Storage::save(json_encode(self::$localizedStructures[$language]), self::$dir . "structure_{$language}.json");
		}
	}
	
	/**
	 * Get all localized versions of the structure
	 * 
	 * @return  void
	 */
	public static function getLocalizedStructures() {
		foreach(Fluid::getConfig('languages') as $language) {
			self::$localizedStructures[$language] = Storage::load(self::$dir . "structure_{$language}.json");
		}
	}
	
	/**
	 * Loop through new structure and save it
	 * 
	 * @param   array   $structure
	 * @param   string  $parent
	 * @return  array
	 */
	public static function loopStructure($structure, $parent = '') {
		$output = array();
		$count = 0;
		foreach($structure as $item) {
			$id = trim($parent.'/'.$item['page'], '/');
			
			if ($id !== $item['id']) {
				self::changeLocalizedStructure($item['id'], $id, $count);
			}
			
			if (isset($item['pages']) && count($item['pages'])) {
				$item['pages'] = self::loopStructure($item['pages'], $id);
			}
			
			unset($item['id']);
			$output[] = $item;
			$count++;
		}
		return $output;
	}
	
	/**
	 * Update an item in the localized structure
	 * 
	 * @param   string  $id
	 * @param   string  $newId
	 * @param   int     $newPos
	 * @return  void
	 */
	public static function changeLocalizedStructure($id, $newId, $newPos) {
		$level = count(explode('/', $id));
		self::$localizedChanges[] = [$level, $id, $newId, $newPos];		
	}
	
	/**
	 * Apply changes to localized structure
	 * 
	 * @return  void
	 */
	public static function applyLocalizedChanges() {
		array_multisort(self::$localizedChanges, SORT_DESC);
		
		foreach(self::$localizedChanges as $change) {
			list($level, $id, $newId, $newPos) = $change;
			foreach(self::$localizedStructures as $language => $localizedStructure) {
				$item = self::removeItem($language, $id);
				if (null !== $item) {
					self::addItem($language, $item, $newId, $newPos);
				}
			}
		}
	}
	
	/**
	 * Remove item from localized structure
	 * 
	 * @param   string  $language
	 * @param   string  $id
	 * @return	array
	 */
	public static function removeItem($language, $id) {
		$path = explode('/', $id);
		$item = self::$localizedStructures[$language];
		
		$arrayKey = '';
		$count = 0;
		do {
			$found = false;
			foreach($item as $key => $value) {
				if (isset($value['page']) && $value['page'] === $path[$count]) {
					if (isset($value['pages']) && count($value['pages'])) {
						$item = $item[$key]['pages'];
						$arrayKey .= "[{$key}]['pages']";
					} else {
						$item = $item[$key];
						$arrayKey .= "[{$key}]";
					}
					$found = true;
					$count++;
					break;
				}
			}
			if (!$found) {
				$item = null;
				$count = -1;			
			}
		} while (isset($path[$count]));
		
		eval('unset(self::$localizedStructures["'.$language.'"]'.$arrayKey.');');
				
		return $item;
	}
	
	/**
	 * Add item to localized structure
	 * 
	 * @param   string  $language
	 * @param   array   $item
	 * @param   string  $id
	 * @param   int     $pos
	 * @return  void
	 */
	public static function addItem($language, $item, $id, $pos) {
		$path = explode('/', $id);
		$name = end($path);
		$path = array_slice($path, 0, -1);
		$parent = self::$localizedStructures[$language];
		
		$arrayKey = '';
		$count = 0;
		while (isset($path[$count])) {
			$found = false;
			foreach($parent as $key => $value) {
				if ($value['page'] === $path[$count]) {
					if (isset($value['pages']) && count($value['pages'])) {
						$parent = $parent[$key]['pages'];
						$arrayKey .= "[{$key}]['pages']";
					} else {
						$parent = $parent[$key];
						$arrayKey .= "[{$key}]";
					}
					$found = true;
					$count++;
					break;
				}
			}
			if (!$found) {
				$count = -1;			
			}
		}
		
		$item['page'] = $name;
		
		$parent = array_merge(
			array_slice($parent, 0, $pos+1),
			array($item),
			array_slice($parent, $pos+1)
		);
		
		eval('self::$localizedStructures["'.$language.'"]'.$arrayKey.' = $parent;');
	}
}