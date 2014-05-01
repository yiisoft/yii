<?php

Yii::import('system.web.CUrlManager');

class CUrlRuleTest extends CTestCase
{
	private $app;

	public function setUp()
	{
		$config=array(
			'basePath'=>dirname(__FILE__),
			'components'=>array(
				'request'=>array(
					'class'=>'TestHttpRequest',
				),
			),
		);
		$_SERVER['HTTP_HOST']='user.example.com';
		$this->app=new TestApplication($config);
	}

	public function testParseUrlMatchValue()
	{
		$rules=array(
			array(
				'route'=>'article/read',
				'pattern'=>'article/<id:\d+>',
				'scriptUrl'=>'/apps/index.php',
				'entries'=>array(
					array(
						'route'=>'article/read',
						'params'=>array(
							'id'=>'123',
							'name1'=>'value1',
						),
						'url'=>'article/123?name1=value1',
					),
					array(
						'route'=>'article/read',
						'params'=>array(
							'id'=>'abc',
							'name1'=>'value1',
						),
						'url'=>false,
					),
					array(
						'route'=>'article/read',
						'params'=>array(
							'id'=>"123\n",
							'name1'=>'value1',
						),
						'url'=>false,
					),
					array(
						'route'=>'article/read',
						'params'=>array(
							'id'=>'0x1',
							'name1'=>'value1',
						),
						'url'=>false,
					),
				),
			),
		);
		$um=new CUrlManager;
		foreach($rules as $rule)
		{
			$this->app->request->baseUrl=null; // reset so that it can be determined based on scriptUrl
			$this->app->request->scriptUrl=$rule['scriptUrl'];
			$ur=new CUrlRule($rule['route'],$rule['pattern']);
			$ur->matchValue=true;
			foreach($rule['entries'] as $entry)
			{
				$url=$ur->createUrl($um,$entry['route'],$entry['params'],'&');
				$this->assertEquals($entry['url'],$url);
			}
		}
	}
}
