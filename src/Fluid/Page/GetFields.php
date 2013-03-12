<?php namespace Fluid\Page;

use Fluid\Fluid;

class GetFields {
	/**
	 * Load a page with fields not rendered by Twig
	 * 
	 * @param   stdClass    $page
	 * @return	void
	 */
	public static function loadPage( $page ) {
		$url = rtrim(Fluid::$urls['staging'], '/').$page->url.'?fluidtoken=yk7vWmfodezhWrRNyUAhoZEVEHCGz4WzVMUWELvZLF7FxNzofzPqsg8eLWBGHvBf';
				
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		
		$result = curl_exec($ch);
		if(curl_errno($ch)){
			throw new Exception("Failed to get URL: " . curl_error($ch), E_USER_NOTICE);
		}
		
		curl_close($ch);
		echo $result;
		die();
		//var_dump($result);die();
	}
	
}