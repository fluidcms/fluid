<?php

namespace Fluid\Models\Page;

use stdClass, Fluid\Models\Site, Fluid\Models\Page;

/**
 * Get Template Data
 *
 * @package fluid
 */
class MergeTemplateData {	
	/**
	 * Merge template data with site and page data.
	 * 
	 * @param   Site    $site
	 * @param   Page    $siteData
	 * @param   array   $variables
	 * @param   array   $data
	 * @return  array
	 */
	public static function merge(Site $site, Page $page, $variables, $data) {
		if (isset($variables['page'])) {
			$page->variables = $variables['page'];
		}
		if (isset($variables['site'])) {
			$site->variables = $variables['site'];
		}
		
		// Merge page data
		if (isset($data['page'])) {
			$page->data = array_merge($data['page'], (is_array($page->data) ? $page->data : array()));
		}
		
		// Merge site data
		if (isset($data['site'])) {
			$site->data = array_merge($data['site'], (is_array($site->data) ? $site->data : array()));
		}
	}
	
	/**
	 * Get template fields and data with fields and data from storage.
	 * 
	 * @param   array   $pageData
	 * @param   array   $siteData
	 * @param   string  $templateContent
	 * @return  array
	 */
	public static function getTemplateData($templateContent) {
		list($language, $page, $templateData) = self::parseTemplateContent($templateContent);

		$variables = array();
		$data = array();
				
		// Add template data
		if (isset($templateData)) {
			foreach($templateData as $item) {
				// Variables
				if ($item instanceof stdClass && $item->type === 'variable') {
					$keys = explode('.', $item->key);
					
					$keyString = '';
					foreach($keys as $key) {
						$key = preg_replace('/[^a-zA-Z0-9_]/', '', $key);
						$keyString .= "['{$key}']";
					}
					
					// Add to variables
					eval('if (!isset($variables'.$keyString.')) { $variables'.$keyString.' = "string"; }');
					
					// Add to data
					eval('$data'.$keyString.' = "'.str_replace("'", "\'", $item->value).'";');
				}
				
				// Arrays
				else if ($item instanceof stdClass && $item->type === 'array') {
					// Array key
					$keys = explode('.', $item->expression);
					
					$keyString = '';
					foreach($keys as $key) {
						$key = preg_replace('/[^a-zA-Z0-9_]/', '', $key);
						$keyString .= "['{$key}']";
					}
					
					// Add array to variables
					foreach($item->variables as $variable) {
						$variable = preg_replace("/^{$item->key}\./", '', $variable);
						$variableKeys = explode('.', $variable);
						$variable = '';
						foreach($variableKeys as $variableKey) {
							$variableKey = preg_replace('/[^a-zA-Z0-9_]/', '', $variableKey);
							$variable .= "['{$variableKey}']";
						}
						eval('if (!isset($variables'.$keyString.$variable.')) { $variables'.$keyString.$variable.' = "string"; }');
					}
					
					// Add array to data
					$count = 0;
					foreach($item->items as $itemKey => $itemValue) {
						$itemKey = preg_replace('/[^a-zA-Z0-9_]/', '', $itemKey);
												
						eval('$data'.$keyString."[{$count}]['{$itemKey}']".' = $itemValue;');
						$count++;
					}				
				}
	
			}
		}
		
		return array($language, $page, $variables, $data);
	}
	
	/**
	 * Parse the template content and return the fields and data.
	 * 
	 * @param   string  $pageContent
	 * @return  array
	 */
	private static function parseTemplateContent($pageContent) {
		$language = preg_replace('{.*<script data-type="fluid-language">(.*?)</script>.*}s', '$1', $pageContent);
		$page = preg_replace('{.*<script data-type="fluid-page">(.*?)</script>.*}s', '$1', $pageContent);
		$data = preg_replace('{.*<script data-type="fluid-data">(.*?)</script>.*}s', '$1', $pageContent);
		
		return array(json_decode($language), json_decode($page), json_decode($data));
	}
}