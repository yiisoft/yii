<?php

Yii::import('system.web.CUrlManager');
Yii::import('system.web.CHttpRequest');

class TestHttpRequest extends CHttpRequest
{
	private $myPathInfo;
	private $myScriptUrl;

	public function getScriptUrl()
	{
		return $this->myScriptUrl;
	}

	public function setScriptUrl($value)
	{
		$this->myScriptUrl=$value;
	}

	public function getPathInfo()
	{
		return $this->myPathInfo;
	}

	public function setPathInfo($value)
	{
		$this->myPathInfo=$value;
	}
}


class CUrlManagerTest extends CTestCase
{
	public function testParseUrlWithPathFormat()
	{
		$rules=array(
			'article/<id:\d+>'=>'article/read',
			'article/<year:\d{4}>/<title>/*'=>'article/read',
			'a/<_a>/*'=>'article',
			'register/*'=>'user',
			'home/*'=>'',
			'ad/*'=>'admin/index/list',
		);
		$entries=array(
			array(
				'pathInfo'=>'article/123',
				'route'=>'article/read',
				'params'=>array('id'=>'123'),
			),
			array(
				'pathInfo'=>'article/123/name/value',
				'route'=>'article/123',
				'params'=>array('name'=>'value'),
			),
			array(
				'pathInfo'=>'article/2000/title goes here',
				'route'=>'article/read',
				'params'=>array('year'=>'2000','title'=>'title goes here'),
			),
			/*
			array(
				'pathInfo'=>'a/edit/title/title goes here',
				'route'=>'article/edit',
				'params'=>array('_a'=>'edit','title'=>'title goes here'),
			),
			*/
			array(
				'pathInfo'=>'article/2000/title goes here/name/value',
				'route'=>'article/read',
				'params'=>array('year'=>'2000','title'=>'title goes here','name'=>'value'),
			),
			array(
				'pathInfo'=>'register/username/admin',
				'route'=>'user',
				'params'=>array('username'=>'admin'),
			),
			array(
				'pathInfo'=>'home/name/value/name1/value1',
				'route'=>'',
				'params'=>array('name'=>'value','name1'=>'value1'),
			),
			array(
				'pathInfo'=>'home2/name/value/name1/value1',
				'route'=>'home2/name',
				'params'=>array('value'=>'name1','value1'=>''),
			),
			array(
				'pathInfo'=>'post',
				'route'=>'post/',
				'params'=>array(),
			),
			array(
				'pathInfo'=>'post/read',
				'route'=>'post/read',
				'params'=>array(),
			),
			array(
				'pathInfo'=>'post/read/id/100',
				'route'=>'post/read',
				'params'=>array('id'=>'100'),
			),
			array(
				'pathInfo'=>'',
				'route'=>'/',
				'params'=>array(),
			),
			array(
				'pathInfo'=>'ad/name/value',
				'route'=>'admin/index/list',
				'params'=>array('name'=>'value'),
			),
			array(
				'pathInfo'=>'admin/name/value',
				'route'=>'admin/name',
				'params'=>array('value'=>''),
			),
		);
		$config=array(
			'basePath'=>dirname(__FILE__),
			'components'=>array(
				'request'=>array(
					'class'=>'TestHttpRequest',
					'scriptUrl'=>'/app/index.php',
				),
			),
		);
		$app=new TestWebApplication($config);
		$app->controllerPath=dirname(__FILE__).DIRECTORY_SEPARATOR.'controllers';
		$request=$app->request;
		$um=new CUrlManager;
		$um->urlSuffix='.html';
		$um->urlFormat='path';
		$um->rules=$rules;
		$um->init($app);
		foreach($entries as $entry)
		{
			$request->pathInfo=$entry['pathInfo'];
			$_GET=array();
			$route=$um->parseUrl($request);
			$this->assertEquals($entry['route'],$route);
			$this->assertEquals($entry['params'],$_GET);
			// test the .html version
			$request->pathInfo=$entry['pathInfo'].'.html';
			$_GET=array();
			$route=$um->parseUrl($request);
			$this->assertEquals($entry['route'],$route);
			$this->assertEquals($entry['params'],$_GET);
		}
	}

	public function testcreateUrlWithPathFormat()
	{
		$rules=array(
			'article/<id:\d+>'=>'article/read',
			'article/<year:\d{4}>/<title>/*'=>'article/read',
			'a/<_a>/*'=>'article',
			'register/*'=>'user',
			'home/*'=>'',
		);
		$config=array(
			'basePath'=>dirname(__FILE__),
			'components'=>array(
				'request'=>array(
					'class'=>'TestHttpRequest',
				),
			),
		);
		$app=new TestWebApplication($config);
		$entries=array(
			array(
				'scriptUrl'=>'/apps/index.php',
				'url'=>'/apps/index.php/article/123?name1=value1',
				'url2'=>'/apps/article/123?name1=value1',
				'url3'=>'/apps/article/123.html?name1=value1',
				'route'=>'article/read',
				'params'=>array(
					'id'=>'123',
					'name1'=>'value1',
				),
			),
			array(
				'scriptUrl'=>'/index.php',
				'url'=>'/index.php/article/123?name1=value1',
				'url2'=>'/article/123?name1=value1',
				'url3'=>'/article/123.html?name1=value1',
				'route'=>'article/read',
				'params'=>array(
					'id'=>'123',
					'name1'=>'value1',
				),
			),
			array(
				'scriptUrl'=>'/apps/index.php',
				'url'=>'/apps/index.php/article/2000/the_title/name1/value1',
				'url2'=>'/apps/article/2000/the_title/name1/value1',
				'url3'=>'/apps/article/2000/the_title/name1/value1.html',
				'route'=>'article/read',
				'params'=>array(
					'year'=>'2000',
					'title'=>'the_title',
					'name1'=>'value1',
				),
			),
			array(
				'scriptUrl'=>'/index.php',
				'url'=>'/index.php/article/2000/the_title/name1/value1',
				'url2'=>'/article/2000/the_title/name1/value1',
				'url3'=>'/article/2000/the_title/name1/value1.html',
				'route'=>'article/read',
				'params'=>array(
					'year'=>'2000',
					'title'=>'the_title',
					'name1'=>'value1',
				),
			),
			array(
				'scriptUrl'=>'/apps/index.php',
				'url'=>'/apps/index.php/post/edit/id/123/name1/value1',
				'url2'=>'/apps/post/edit/id/123/name1/value1',
				'url3'=>'/apps/post/edit/id/123/name1/value1.html',
				'route'=>'post/edit',
				'params'=>array(
					'id'=>'123',
					'name1'=>'value1',
				),
			),
		);
		foreach($entries as $entry)
		{
			$app->request->baseUrl=null; // reset so that it can be determined based on scriptUrl
			$app->request->scriptUrl=$entry['scriptUrl'];
			$um=new CUrlManager;
			$um->urlFormat='path';
			$um->rules=$rules;
			$um->init($app);
			$url=$um->createUrl($entry['route'],$entry['params']);
			$this->assertEquals($entry['url'],$url);

			$um=new CUrlManager;
			$um->urlFormat='path';
			$um->rules=$rules;
			$um->init($app);
			$um->showScriptName=false;
			$url=$um->createUrl($entry['route'],$entry['params']);
			$this->assertEquals($entry['url2'],$url);

			$um->urlSuffix='.html';
			$url=$um->createUrl($entry['route'],$entry['params']);
			$this->assertEquals($entry['url3'],$url);
		}
	}

	public function testParseUrlWithGetFormat()
	{
		$config=array(
			'basePath'=>dirname(__FILE__),
			'components'=>array(
				'request'=>array(
					'class'=>'TestHttpRequest',
					'scriptUrl'=>'/app/index.php',
				),
			),
		);
		$entries=array(
			array(
				'route'=>'article/read',
				'name'=>'value',
			),
		);
		$app=new TestWebApplication($config);
		$request=$app->request;
		$um=new CUrlManager;
		$um->urlFormat='get';
		$um->routeVar='route';
		$um->init($app);
		foreach($entries as $entry)
		{
			$_GET=$entry;
			$route=$um->parseUrl($request);
			$this->assertEquals($entry['route'],$route);
			$this->assertEquals($_GET,$entry);
		}
	}

	public function testCreateUrlWithGetFormat()
	{
		$config=array(
			'basePath'=>dirname(__FILE__),
			'components'=>array(
				'request'=>array(
					'class'=>'TestHttpRequest',
				),
			),
		);
		$app=new TestWebApplication($config);
		$entries=array(
			array(
				'scriptUrl'=>'/apps/index.php',
				'url'=>'/apps/index.php?route=article/read&name=value&name1=value1',
				'url2'=>'/apps/?route=article/read&name=value&name1=value1',
				'route'=>'article/read',
				'params'=>array(
					'name'=>'value',
					'name1'=>'value1',
				),
			),
			array(
				'scriptUrl'=>'/index.php',
				'url'=>'/index.php?route=article/read&name=value&name1=value1',
				'url2'=>'/?route=article/read&name=value&name1=value1',
				'route'=>'article/read',
				'params'=>array(
					'name'=>'value',
					'name1'=>'value1',
				),
			),
		);
		foreach($entries as $entry)
		{
			$app->request->baseUrl=null;
			$app->request->scriptUrl=$entry['scriptUrl'];
			$um=new CUrlManager;
			$um->urlFormat='get';
			$um->routeVar='route';
			$um->init($app);
			$url=$um->createUrl($entry['route'],$entry['params'],'&');
			$this->assertEquals($url,$entry['url']);

			$um=new CUrlManager;
			$um->urlFormat='get';
			$um->routeVar='route';
			$um->showScriptName=false;
			$um->init($app);
			$url=$um->createUrl($entry['route'],$entry['params'],'&');
			$this->assertEquals($url,$entry['url2']);
		}
	}
}
