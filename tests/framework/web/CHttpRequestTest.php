<?php

class CHttpRequestTest extends CTestCase
{
	/**
	 * @covers CHttpRequest::parseAcceptHeader
	 * @dataProvider acceptHeaderDataProvider
	 */
	public function testParseAcceptHeader($header,$result,$errorString='Parse of header did not give expected result')
	{
		$this->assertEquals($result,CHttpRequest::parseAcceptHeader($header),$errorString);
	}

	/**
	 * @covers CHttpRequest::parseAcceptLanguagesHeader
	 * @dataProvider acceptLanguagesHeaderDataProvider
	 */
	public function testParseAcceptLanguagesHeader($header,$result,$errorString='Parse of header did not give expected result')
	{
		$this->assertEquals($result,CHttpRequest::parseAcceptLanguagesHeader($header),$errorString);
	}

	/**
	 * @covers CHttpRequest::compareAcceptTypes
	 * @dataProvider acceptContentTypeArrayMapDataProvider
	 */
	public function testCompareAcceptTypes($a,$b,$result,$errorString='Compare of content type array maps did not give expected preference')
	{
		$this->assertEquals($result,CHttpRequest::compareAcceptTypes($a,$b),$errorString);
		// make sure that inverse comparison holds
		$this->assertEquals($result*-1,CHttpRequest::compareAcceptTypes($b,$a),'(Inverse) '.$errorString);
	}

	/**
	 * @covers CHttpRequest::compareAcceptLanguages
	 * @dataProvider acceptLanguagesArrayMapDataProvider
	 */
	public function testCompareAcceptLanguages($a,$b,$result,$errorString='Compare of content type array maps did not give expected preference')
	{
		$this->assertEquals($result,CHttpRequest::compareAcceptLanguages($a,$b),$errorString);
		// make sure that inverse comparison holds
		$this->assertEquals($result*-1,CHttpRequest::compareAcceptLanguages($b,$a),'(Inverse) '.$errorString);
	}

	public function acceptHeaderDataProvider()
	{
		return array(
			// null header
			array(
				null,
				array(),
				'Parsing null Accept header did not return empty array',
			),
			// empty header
			array(
				'',
				array(),
				'Parsing empty Accept header did not return empty array',
			),
			// nonsense header, containing no valid accept types (but containing the characters that the header is split on)
			array(
				'gsf,\'yas\'erys"rt;,";s,y s;,',
				array(),
				'Parsing completely invalid Accept header did not return empty array',
			),
			// valid header containing only content types
			array(
				'application/xhtml+xml,text/html,*/json,image/png',
				array(
					array(
						'type'=>'application',
						'subType'=>'xhtml',
						'baseType'=>'xml',
						'position'=>0,
						'params'=>array(
							'q'=>1,
						),
					),
					array(
						'type'=>'text',
						'subType'=>'html',
						'baseType'=>null,
						'position'=>1,
						'params'=>array(
							'q'=>1,
						),
					),
					array(
						'type'=>'*',
						'subType'=>'json',
						'baseType'=>null,
						'position'=>2,
						'params'=>array(
							'q'=>1,
						),
					),
					array(
						'type'=>'image',
						'subType'=>'png',
						'baseType'=>null,
						'position'=>3,
						'params'=>array(
							'q'=>1,
						),
					),
				),
				'Parsing valid Accept header containing only content types did not return correct result',
			),
			// valid header containing all details
			array(
				'application/xhtml+xml;q=0.9,text/html,*/json;q=4;level=three,image/png;a=1;b=2;c=3',
				array(
					array(
						'type'=>'application',
						'subType'=>'xhtml',
						'baseType'=>'xml',
						'position'=>0,
						'params'=>array(
							'q'=>0.9,
						),
					),
					array(
						'type'=>'text',
						'subType'=>'html',
						'baseType'=>null,
						'position'=>1,
						'params'=>array(
							'q'=>1,
						),
					),
					array(
						'type'=>'*',
						'subType'=>'json',
						'baseType'=>null,
						'position'=>2,
						'params'=>array(
							'q'=>1,
							'level'=>'three',
						),
					),
					array(
						'type'=>'image',
						'subType'=>'png',
						'baseType'=>null,
						'position'=>3,
						'params'=>array(
							'q'=>1,
							'a'=>1,
							'b'=>2,
							'c'=>3,
						),
					),
				),
				'Parsing valid Accept header containing all details did not return correct result',
			),
			// partially valid header containing all details (no , after */json)
			array(
				'application/xhtml+xml;q=0.9,text/html,*/json;q=4;level=three image/png;a=1;b=2;c=3',
				array(
					array(
						'type'=>'application',
						'subType'=>'xhtml',
						'baseType'=>'xml',
						'position'=>0,
						'params'=>array(
							'q'=>0.9,
						),
					),
					array(
						'type'=>'text',
						'subType'=>'html',
						'baseType'=>null,
						'position'=>1,
						'params'=>array(
							'q'=>1,
						),
					),
					array(
						'type'=>'*',
						'subType'=>'json',
						'baseType'=>null,
						'position'=>2,
						'params'=>array(
							'q'=>1,
							'level'=>'three',
						),
					),
				),
				'Parsing partially valid Accept header containing all details did not return correct result',
			),
		);
	}

	public function acceptLanguagesHeaderDataProvider()
	{
		return array(
			// null header
			array(
				null,
				array(),
				'Parsing null Accept-Language header did not return empty array',
			),
			// empty header
			array(
				'',
				array(),
				'Parsing empty Accept-Language header did not return empty array',
			),
			// header containing multiple languages without any q values (so should revert to original order)
			array(
				'ru,en,fr,cn,es',
				array(
					array(
						'q'=>1,
						'language'=>'ru',
						'position'=>0,
					),
					array(
						'q'=>1,
						'language'=>'en',
						'position'=>1,
					),
					array(
						'q'=>1,
						'language'=>'fr',
						'position'=>2,
					),
					array(
						'q'=>1,
						'language'=>'cn',
						'position'=>3,
					),
					array(
						'q'=>1,
						'language'=>'es',
						'position'=>4,
					),
				),
				'Parsing languages without q values did not return expected order',
			),
			// valid header with q values
			array(
				'en;q=0.8,fr;q=0.9,ru;q=0.777,cn;q=0.90001',
				array(
					array(
						'q'=>0.8,
						'language'=>'en',
						'position'=>0,
					),
					array(
						'q'=>0.9,
						'language'=>'fr',
						'position'=>1,
					),
					array(
						'q'=>0.777,
						'language'=>'ru',
						'position'=>2,
					),
					array(
						'q'=>0.90001,
						'language'=>'cn',
						'position'=>3,
					),
				),
				'Parsing valid Accept-Language header with q values did not return correct result',
			),
			// valid header with multiple q values and multiple missing q values
			array(
				'cn,es,fr;q=0.5,ru;q=0.5,en;q=1',
				array(
					array(
						'q'=>1,
						'language'=>'cn',
						'position'=>0,
					),
					array(
						'q'=>1,
						'language'=>'es',
						'position'=>1,
					),
					array(
						'q'=>0.5,
						'language'=>'fr',
						'position'=>2,
					),
					array(
						'q'=>0.5,
						'language'=>'ru',
						'position'=>3,
					),
					array(
						'q'=>1,
						'language'=>'en',
						'position'=>4,
					),
				),
				'Parsing valid Accept header containing all details did not return correct result',
			),
		);
	}

	public function acceptContentTypeArrayMapDataProvider()
	{
		return array(
			array(
				array(
					'type'=>'application',
					'subType'=>'xhtml',
					'baseType'=>'xml',
					'position'=>0,
					'params'=>array(
						'q'=>0.99,
					),
				),
				array(
					'type'=>'text',
					'subType'=>'html',
					'baseType'=>null,
					'position'=>1,
					'params'=>array(
						'q'=>(double)1,
					),
				),
				1,
				'Comparing different q did not assign correct preference',
			),
			array(
				array(
					'type'=>'application',
					'subType'=>'xhtml',
					'baseType'=>'xml',
					'position'=>0,
					'params'=>array(
						'q'=>0.5,
					),
				),
				array(
					'type'=>'*',
					'subType'=>'html',
					'baseType'=>null,
					'position'=>1,
					'params'=>array(
						'q'=>0.5,
					),
				),
				-1,
				'Comparing type wildcard with specific type did not assign correct preference',
			),
			array(
				array(
					'type'=>'application',
					'subType'=>'*',
					'baseType'=>'xml',
					'position'=>0,
					'params'=>array(
						'q'=>0.5,
					),
				),
				array(
					'type'=>'text',
					'subType'=>'html',
					'baseType'=>null,
					'position'=>1,
					'params'=>array(
						'q'=>0.5,
					),
				),
				1,
				'Comparing subType wildcard with specific subType did not assign correct preference',
			),
			array(
				array(
					'type'=>'*',
					'subType'=>'xhtml',
					'baseType'=>'xml',
					'position'=>0,
					'params'=>array(
						'q'=>0.9,
						'foo'=>'bar2',
					),
				),
				array(
					'type'=>'*',
					'subType'=>'html',
					'baseType'=>null,
					'position'=>1,
					'params'=>array(
						'q'=>0.9,
						'foo'=>'bar',
						'test'=>'drive',
					),
				),
				1,
				'Comparing different number of params did not assign correct preference',
			),
			array(
				array(
					'type'=>'*',
					'subType'=>'xhtml',
					'baseType'=>'xml',
					'position'=>0,
					'params'=>array(
						'q'=>0.9,
						'foo'=>'bar',
					),
				),
				array(
					'type'=>'*',
					'subType'=>'html',
					'baseType'=>null,
					'position'=>1,
					'params'=>array(
						'q'=>0.9,
						'foo'=>'bar',
					),
				),
				-1,
				'Comparing equal type, subType, q and number of params did not correctly take original position into account',
			),
		);
	}

	public function acceptLanguagesArrayMapDataProvider()
	{
		return array(
			array(
				array(
					'q'=>1,
					'language'=>'cn',
					'position'=>0,
				),
				array(
					'q'=>1,
					'language'=>'es',
					'position'=>1,
				),
				-1,
				'Comparing equal q with different position did not assign correct preference',
			),
			array(
				array(
					'q'=>0.5,
					'language'=>'fr',
					'position'=>3,
				),
				array(
					'q'=>0.9,
					'language'=>'ru',
					'position'=>3,
				),
				1,
				'Comparing different q values did not assign correct preference',
			),
			array(
				array(
					'q'=>0.500001,
					'language'=>'fr',
					'position'=>3,
				),
				array(
					'q'=>0.5,
					'language'=>'ru',
					'position'=>3,
				),
				-1,
				'Comparing very slightly different q values did not assign correct preference',
			),
		);
	}
}