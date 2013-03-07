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
			$this->markTestSkipped('DOM extension is required.');
	}

	/**
	* Test generation of WSDL xml file
	*/
	public function testGenerateWsdl(){

		$generator=new CWsdlGenerator();

		// we use any URL location since unit test is executed via CLI and not real HTTP request
		$wsdl=$generator->generateWsdl('SoapController','http://10.20.30.40/index.php?r=soap/calculator&ws=1');

		// try to save XML output for manual checkup
		// uncomment to save WSDL into file and test also a PHP soapClient
		/*
			$this->path=Yii::getPathOfAlias('application.runtime').DIRECTORY_SEPARATOR.'soap-wsdl-test.xml';
			if(is_file($this->path))
				unlink($this->path);
			$this->assertTrue( 0 < file_put_contents($this->path, $wsdl), 'Failed saving WSDL into ['.$this->path.']');

			// create SOAP client and check provided actions and types
			$client=new SoapClient($this->path);
			$functions=$client->__getFunctions();
			$this->assertTrue( count($functions) > 0 );
			$types=$client->__getTypes();
			$this->assertTrue( count($types) > 0 );
		*/

		$xml=simplexml_load_string($wsdl);
		$this->assertTrue($xml instanceOf SimpleXMLElement);

		// check input attribute with all attributes (minOccurs, maxOccurs, nillable)
		$node=$xml->xpath('//xsd:element[@name="subject"]');

		$minOccurs=(string)$node[0]->attributes()->minOccurs;
		$this->assertTrue($minOccurs==='1');

		$maxOccurs=(string)$node[0]->attributes()->maxOccurs;
		$this->assertTrue($maxOccurs==='1');

		$nillable=(string)$node[0]->attributes()->nillable;
		$this->assertTrue($nillable==='false');

		$type=(string)$node[0]->attributes()->type;
		$this->assertTrue($type==='xsd:integer');

		// check input attribute with only nillable
		$node=$xml->xpath('//xsd:element[@name="ins_start_date"]');

		$minOccurs=(string)$node[0]->attributes()->minOccurs;
		$this->assertTrue($minOccurs===''); // null converts to empty string

		$maxOccurs=(string)$node[0]->attributes()->maxOccurs;
		$this->assertTrue($maxOccurs==='');

		$nillable=(string)$node[0]->attributes()->nillable;
		$this->assertTrue($nillable==='true');

		$type=(string)$node[0]->attributes()->type;
		$this->assertTrue($type==='xsd:date');

		// check some output attribute
		$node=$xml->xpath('//xsd:element[@name="company_key"]');

		$minOccurs=(string)$node[0]->attributes()->minOccurs;
		$this->assertTrue($minOccurs==='1');

		$maxOccurs=(string)$node[0]->attributes()->maxOccurs;
		$this->assertTrue($maxOccurs==='1');

		$nillable=(string)$node[0]->attributes()->nillable;
		$this->assertTrue($nillable==='false');

		$type=(string)$node[0]->attributes()->type;
		$this->assertTrue($type==='xsd:string');

		// check soap indicator-sequence
		$nodes=$xml->xpath('//xsd:complexType[@name="SoapPovCalculationInput"]/xsd:sequence/*');
		$this->assertTrue(is_array($nodes) && count($nodes)>10); // there is 13 child nodes

		// check soap indicator-choice
		$nodes=$xml->xpath('//xsd:complexType[@name="SoapInsurerPerson"]/xsd:choice/*');
		$this->assertTrue(is_array($nodes) && count($nodes)==2); // choose either physical or juristic person

		// check soap custom XML nodes injected into WSDL
		//$node=$xml->xpath('//xsd:complexType[@name="SoapInsurerPersonPhysical"]/xsd:sequence/xsd:choice/xsd:element[name="age"]');
		$nodes=$xml->xpath('//xsd:complexType[@name="SoapInsurerPersonPhysical"]/xsd:sequence/xsd:choice/*');
		$type=(string)$nodes[1]->attributes()->type;
		$this->assertTrue($type==='xsd:date');

		// check maxOccurs=unbounded
		$node=$xml->xpath('//xsd:complexType[@name="SoapInsurerPersonPhysical"]/xsd:sequence/xsd:element[@name="studentCardNumber"]');
		$maxOccurs=(string)$node[0]->attributes()->maxOccurs;
		$this->assertTrue($maxOccurs==='unbounded');
	}

	/**
	* Test generation of HTML documentation
	*/
	public function testGenerateHtmlDocumentation(){

		$generator=new CWsdlGenerator();

		// we dont care this time about WSDL, we simple must craete some parsed data
		$generator->generateWsdl('SoapController','http://10.20.30.40/index.php?r=soap/calculator&ws=1');

		// get HTML documentation
		$html=$generator->buildHtmlDocs(true);

		// uncomment to save WSDL into file
		/*
		$this->path=Yii::getPathOfAlias('application.runtime').DIRECTORY_SEPARATOR.'soap-wsdl-test-doc.html';
		if(is_file($this->path))
			unlink($this->path);
		$this->assertTrue( 0 < file_put_contents($this->path, $html), 'Failed saving WSDL into ['.$this->path.']');
		*/

		// check we have table for object SoapPovCalculationInput
		$this->assertTrue(false!==strpos($html,'SoapPovCalculationInput'));
		// check column Attribute in table SoapPovCalculationInput
		$this->assertTrue(false!==strpos($html,'use_kind'));
		// check column Type in table SoapPovCalculationInput
		$this->assertTrue(false!==strpos($html,'integer'));
		// check column Required in table SoapPovCalculationInput
		$this->assertTrue(false!==strpos($html,'unbounded'));
		// check column Description in table SoapPovCalculationInput
		$this->assertTrue(false!==strpos($html,'the date of birth RRRR.MM.DD'));
		// check column Example in table SoapPovCalculationInput
		$this->assertTrue(false!==strpos($html,'85HN65'));
	}

}