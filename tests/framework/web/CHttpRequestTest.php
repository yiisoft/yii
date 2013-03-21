<?php

class CHttpRequestTest extends CTestCase
{
	/**
	 * @covers CHttpRequest::parseAcceptHeader
	 */
	public function testParseAcceptHeader()
	{
		$tests=array(
			// null header
			array(
				'header'=>null,
				'result'=>array(),
				'error'=>'Parsing null Accept header did not return empty array',
			),
			// empty header
			array(
				'header'=>'',
				'result'=>array(),
				'error'=>'Parsing empty Accept header did not return empty array',
			),
			// nonsense header, containing no valid accept types (but containing the characters that the header is split on)
			array(
				'header'=>'gsf,\'yas\'erys"rt;,";s,y s;,',
				'result'=>array(),
				'error'=>'Parsing completely invalid Accept header did not return empty array',
			),
			// valid header containing only content types
			array(
				'header'=>'application/xhtml+xml,text/html,*/json,image/png',
				'result'=>array(
					array(
						'type'=>'application',
						'subType'=>'xhtml',
						'baseType'=>'xml',
						'params'=>array(
							'q'=>1,
						),
					),
					array(
						'type'=>'text',
						'subType'=>'html',
						'baseType'=>null,
						'params'=>array(
							'q'=>1,
						),
					),
					array(
						'type'=>'*',
						'subType'=>'json',
						'baseType'=>null,
						'params'=>array(
							'q'=>1,
						),
					),
					array(
						'type'=>'image',
						'subType'=>'png',
						'baseType'=>null,
						'params'=>array(
							'q'=>1,
						),
					),
				),
				'error'=>'Parsing valid Accept header containing only content types did not return correct result',
			),
			// valid header containing all details
			array(
				'header'=>'application/xhtml+xml;q=0.9,text/html,*/json;q=4;level=three,image/png;a=1;b=2;c=3',
				'result'=>array(
					array(
						'type'=>'application',
						'subType'=>'xhtml',
						'baseType'=>'xml',
						'params'=>array(
							'q'=>0.9,
						),
					),
					array(
						'type'=>'text',
						'subType'=>'html',
						'baseType'=>null,
						'params'=>array(
							'q'=>1,
						),
					),
					array(
						'type'=>'*',
						'subType'=>'json',
						'baseType'=>null,
						'params'=>array(
							'q'=>1,
							'level'=>'three',
						),
					),
					array(
						'type'=>'image',
						'subType'=>'png',
						'baseType'=>null,
						'params'=>array(
							'q'=>1,
							'a'=>1,
							'b'=>2,
							'c'=>3,
						),
					),
				),
				'error'=>'Parsing valid Accept header containing all details did not return correct result',
			),
			// partially valid header containing all details (no , after */json)
			array(
				'header'=>'application/xhtml+xml;q=0.9,text/html,*/json;q=4;level=three image/png;a=1;b=2;c=3',
				'result'=>array(
					array(
						'type'=>'application',
						'subType'=>'xhtml',
						'baseType'=>'xml',
						'params'=>array(
							'q'=>0.9,
						),
					),
					array(
						'type'=>'text',
						'subType'=>'html',
						'baseType'=>null,
						'params'=>array(
							'q'=>1,
						),
					),
					array(
						'type'=>'*',
						'subType'=>'json',
						'baseType'=>null,
						'params'=>array(
							'q'=>1,
							'level'=>'three',
						),
					),
				),
				'error'=>'Parsing partially valid Accept header containing all details did not return correct result',
			),
		);

		foreach($tests as $test) {
			$this->assertEquals($test['result'],CHttpRequest::parseAcceptHeader($test['header']),$test['error']);
		}
	}

	/**
	 * @covers CHttpRequest::compareAcceptTypes
	 */
	public function testCompareAcceptTypes()
	{
		$tests=array(
			array(
				'a' => array(
					'type'=>'application',
					'subType'=>'xhtml',
					'baseType'=>'xml',
					'params'=>array(
						'q'=>0.99,
					),
				),
				'b' => array(
					'type'=>'text',
					'subType'=>'html',
					'baseType'=>null,
					'params'=>array(
						'q'=>(double)1,
					),
				),
				'result'=>1,
				'error'=>'Comparing different q did not assign correct preference',
			),
			array(
				'a' => array(
					'type'=>'application',
					'subType'=>'xhtml',
					'baseType'=>'xml',
					'params'=>array(
						'q'=>0.5,
					),
				),
				'b' => array(
					'type'=>'*',
					'subType'=>'html',
					'baseType'=>null,
					'params'=>array(
						'q'=>0.5,
					),
				),
				'result'=>-1,
				'error'=>'Comparing type wildcard with specific type did not assign correct preference',
			),
			array(
				'a' => array(
					'type'=>'application',
					'subType'=>'*',
					'baseType'=>'xml',
					'params'=>array(
						'q'=>0.5,
					),
				),
				'b' => array(
					'type'=>'text',
					'subType'=>'html',
					'baseType'=>null,
					'params'=>array(
						'q'=>0.5,
					),
				),
				'result'=>1,
				'error'=>'Comparing subType wildcard with specific subType did not assign correct preference',
			),
			array(
				'a' => array(
					'type'=>'*',
					'subType'=>'xhtml',
					'baseType'=>'xml',
					'params'=>array(
						'q'=>0.9,
						'foo'=>'bar2',
					),
				),
				'b' => array(
					'type'=>'*',
					'subType'=>'html',
					'baseType'=>null,
					'params'=>array(
						'q'=>0.9,
						'foo'=>'bar',
						'test'=>'drive',
					),
				),
				'result'=>1,
				'error'=>'Comparing different number of params did not assign correct preference',
			),
			array(
				'a' => array(
					'type'=>'*',
					'subType'=>'xhtml',
					'baseType'=>'xml',
					'params'=>array(
						'q'=>0.9,
						'foo'=>'bar',
					),
				),
				'b' => array(
					'type'=>'*',
					'subType'=>'html',
					'baseType'=>null,
					'params'=>array(
						'q'=>0.9,
						'foo'=>'bar',
					),
				),
				'result'=>0,
				'error'=>'Comparing equal type, subType, q and number of params did not return equality',
			),
		);

		foreach($tests as $test)
			$this->assertEquals($test['result'],CHttpRequest::compareAcceptTypes($test['a'],$test['b']),$test['error']);
		// make sure that inverse comparison holds
		foreach($tests as $test)
			$this->assertEquals($test['result']*-1,CHttpRequest::compareAcceptTypes($test['b'],$test['a']),'Inverse '.$test['error']);
	}
}