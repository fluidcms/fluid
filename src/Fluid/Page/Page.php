<?php namespace Fluid\Page;

use Fluid\Structure\Structure;

class Page extends \Fluid\Model {	
	/**
	 * Get data for a page
	 * 
	 * @param		
	 * @return	
	 */
	public static function getPage( $page ) {
		return [];
	}
	
	/**
	 * Get data for a page
	 * 
	 * @param		
	 * @return	
	 */
	public static function getFields( $page ) {
		$page = Structure::findPage($page);
		GetFields::loadPage( $page );
	}
	
	/**
	 * Get fields and data from template with fields and data from storage.
	 * 
	 * @param   string  $pageContent
	 * @param   string  $pageUrl
	 * @return	// !! xxx
	 */
	public static function getAllData( $pageContent, $pageUrl ) {
		list($page, $templateData) = self::parsePageContent($pageContent);
		return self::mergeTemplateData(self::getPage($page), $templateData);
	}
	
	/**
	 * Merge template fields and data with fields and data from storage.
	 * 
	 * @param   array   $storageData
	 * @param   array   $templateData
	 * @return  array
	 */
	private static function mergeTemplateData( $storageData, $templateData ) {
		$data = array();
		$order = array();
		
		// Add template data
		foreach($templateData as $item) {
			if ($item instanceof \stdClass && $item->type === 'variable') {
				$keys = explode('.', $item->key);
				
				$code = '$data';
				foreach($keys as $key) {
					$code .= '["'.$key.'"]';
				}
				$code .= ' = "'.str_replace("'", "\'", $item->value).'";';
				eval($code);
			}
		}
		
		// !! Merge storage data
		
		return $data;
	}
	
	/**
	 * Parse the page content and return the page info and the fields and data.
	 * 
	 * @param   string  $pageContent
	 * @return  array
	 */
	private static function parsePageContent( $pageContent ) {
		$pageContents = explode(PHP_EOL, $pageContent, 2);
		$data = preg_replace('{^<script data-type="fluid-data">(.*)</script>$}', '$1', $pageContents[0]);
		$data = json_decode($data);
		$page = $data[0];
		array_shift($data);
		return array($page, $data);
	}
}