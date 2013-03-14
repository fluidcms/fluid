<?php

namespace Fluid\Tests\Models;

use Fluid, Fluid\Models\Language, PHPUnit_Framework_TestCase;

class LanguageTest extends PHPUnit_Framework_TestCase {
	/**
	 * @dataProvider languageProvider
	 */
	public function testGetLanguage($languages) {		
		new Fluid\Fluid(array('languages'=>$languages));
		
		$actual = Language::getLanguages();
		
		$this->assertInternalType('array', $actual, 'Function did not return an array');
		$this->assertEquals($languages, $actual);
    }
    
    public function languageProvider() {
	    return array(
	    	array(
	    		array(
	    			'en-US'=>'English', 
	    			'de-DE'=>'Deutsch'
	    		)
	    	)
	    );
    }
}
