<?php

// path to tested class
Yii::import('system.web.services.CWsdlGenerator');

// paths to input-output objects
Yii::import('application.framework.web.services.*');

// path to soap (fake) controller
Yii::import('application.framework.web.controllers.*');

/**
* Unit test for Soap WSDL generator
*/
class CWsdlGeneratorTest extends CTestCase{

	/**
	* Path where we will try to save generated WSDL
	*/
	protected $path;

	public function setUp(){

		if(!extension_loaded('dom'))
		{
			$this->markTestSkipped('DOM extension is required.');
		}

		$this->path = Yii::getPathOfAlias('application.runtime').DIRECTORY_SEPARATOR.'soap-wsdl-test.xml';

		if(is_file($this->path))
		{
			unlink($this->path);
		}
	}
	
	public function testGenerateWsdl(){
		
		$generator = new CWsdlGenerator();
		
		// we use any URL location since unit test is executed via CLI and not real HTTP request
		$wsdl = $generator->generateWsdl('SoapController', 'http://10.20.30.40/index.php?r=soap/calculator&ws=1');
		
		// try to save XML output for manual checkup
		// uncomment to save WSDL into file
		// $this->assertTrue( 0 < file_put_contents($this->path, $wsdl), 'Failed saving WSDL into ['.$this->path.']');
		
		// let's check some random input attribute
		$xml = simplexml_load_string($wsdl);
		$node = $xml->xpath('//xsd:element[@name="subject"]');
		
		$minOccurs = (string)$node[0]->attributes()->minOccurs;
		$this->assertTrue($minOccurs === '1');

		$maxOccurs = (string)$node[0]->attributes()->maxOccurs;
		$this->assertTrue($maxOccurs === '1');

		$nillable = (string)$node[0]->attributes()->nillable;
		$this->assertTrue($nillable === 'false');

	}
	
}