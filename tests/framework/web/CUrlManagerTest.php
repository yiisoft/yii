<?php

Yii::import('system.web.CUrlManager');

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
			'<c:(post|comment)>/<id:\d+>/<a:(create|update|delete)>'=>'<c>/<a>',
			'<c:(post|comment)>/<id:\d+>'=>'<c>/view',
			'<c:(post|comment)>s/*'=>'<c>/list',
			'http://<user:\w+>.example.com/<lang:\w+>/profile'=>'user/profile',
			'currency/<c:\p{Sc}>'=>'currency/info',
		);
		$entries=array(
			array(
				'pathInfo'=>'article/123',
				'route'=>'article/read',
				'params'=>array('id'=>'123'),
			),
			array(
				'pathInfo'=>'article/123/name/value',
				'route'=>'article/123/name/value',
				'params'=>array(),
			),
			array(
				'pathInfo'=>'article/2000/title goes here',
				'route'=>'article/read',
				'params'=>array('year'=>'2000','title'=>'title goes here'),
			),
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
				'route'=>'home2/name/value/name1/value1',
				'params'=>array(),
			),
			array(
				'pathInfo'=>'post',
				'route'=>'post',
				'params'=>array(),
			),
			array(
				'pathInfo'=>'post/read',
				'route'=>'post/read',
				'params'=>array(),
			),
			array(
				'pathInfo'=>'post/read/id/100',
				'route'=>'post/read/id/100',
				'params'=>array(),
			),
			array(
				'pathInfo'=>'',
				'route'=>'',
				'params'=>array(),
			),
			array(
				'pathInfo'=>'ad/name/value',
				'route'=>'admin/index/list',
				'params'=>array('name'=>'value'),
			),
			array(
				'pathInfo'=>'admin/name/value',
				'route'=>'admin/name/value',
				'params'=>array(),
			),
			array(
				'pathInfo'=>'posts',
				'route'=>'post/list',
				'params'=>array(),
			),
			array(
				'pathInfo'=>'posts/page/3',
				'route'=>'post/list',
				'params'=>array('page'=>3),
			),
			array(
				'pathInfo'=>'post/3',
				'route'=>'post/view',
				'params'=>array('id'=>3),
			),
			array(
				'pathInfo'=>'post/3/delete',
				'route'=>'post/delete',
				'params'=>array('id'=>3),
			),
			array(
				'pathInfo'=>'post/3/delete/a',
				'route'=>'post/3/delete/a',
				'params'=>array(),
			),
			array(
				'pathInfo'=>'en/profile',
				'route'=>'user/profile',
				'params'=>array('user'=>'admin','lang'=>'en'),
			),
			array(
				'pathInfo'=>'currency/＄',
				'route'=>'currency/info',
				'params'=>array('c'=>'＄'),
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
		$app=new TestApplication($config);
		$app->controllerPath=dirname(__FILE__).DIRECTORY_SEPARATOR.'controllers';
		$request=$app->request;
		$_SERVER['HTTP_HOST']='admin.example.com';
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
			'<c:(post|comment)>/<id:\d+>/<a:(create|update|delete)>'=>'<c>/<a>',
			'<c:(post|comment)>/<id:\d+>'=>'<c>/view',
			'<c:(post|comment)>s/*'=>'<c>/list',
			'http://<user:\w+>.example.com/<lang:\w+>/profile'=>'user/profile',
			'currency/<c:\p{Sc}>'=>'currency/info',
		);
		$config=array(
			'basePath'=>dirname(__FILE__),
			'components'=>array(
				'request'=>array(
					'class'=>'TestHttpRequest',
				),
			),
		);
		$_SERVER['HTTP_HOST']='user.example.com';
		$app=new TestApplication($config);
		$entries=array(
			array(
				'scriptUrl'=>'/apps/index.php',
				'url'=>'/apps/index.php/post/123?name1=value1',
				'url2'=>'/apps/post/123?name1=value1',
				'url3'=>'/apps/post/123.html?name1=value1',
				'route'=>'post/view',
				'params'=>array(
					'id'=>'123',
					'name1'=>'value1',
				),
			),
			array(
				'scriptUrl'=>'/apps/index.php',
				'url'=>'/apps/index.php/post/123/update?name1=value1',
				'url2'=>'/apps/post/123/update?name1=value1',
				'url3'=>'/apps/post/123/update.html?name1=value1',
				'route'=>'post/update',
				'params'=>array(
					'id'=>'123',
					'name1'=>'value1',
				),
			),
			array(
				'scriptUrl'=>'/apps/index.php',
				'url'=>'/apps/index.php/posts/page/123',
				'url2'=>'/apps/posts/page/123',
				'url3'=>'/apps/posts/page/123.html',
				'route'=>'post/list',
				'params'=>array(
					'page'=>'123',
				),
			),
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
			array(
				'scriptUrl'=>'/index.php',
				'url'=>'http://admin.example.com/en/profile',
				'url2'=>'http://admin.example.com/en/profile',
				'url3'=>'http://admin.example.com/en/profile.html',
				'route'=>'user/profile',
				'params'=>array(
					'user'=>'admin',
					'lang'=>'en',
				),
			),
			array(
				'scriptUrl'=>'/index.php',
				'url'=>'/en/profile',
				'url2'=>'/en/profile',
				'url3'=>'/en/profile.html',
				'route'=>'user/profile',
				'params'=>array(
					'user'=>'user',
					'lang'=>'en',
				),
			),
			array(
				'scriptUrl'=>'/index.php',
				'url'=>'/index.php/currency/%EF%BC%84',
				'url2'=>'/currency/%EF%BC%84',
				'url3'=>'/currency/%EF%BC%84.html',
				'route'=>'currency/info',
				'params'=>array(
					'c'=>'＄',
				),
			),
		);
		foreach($entries as $entry)
		{
			$app->request->baseUrl=null; // reset so that it can be determined based on scriptUrl
			$app->request->scriptUrl=$entry['scriptUrl'];
			for($matchValue=0;$matchValue<2;$matchValue++)
			{
				$um=new CUrlManager;
				$um->urlFormat='path';
				$um->rules=$rules;
				$um->matchValue=$matchValue!=0;
				$um->init($app);
				$url=$um->createUrl($entry['route'],$entry['params']);
				$this->assertEquals($entry['url'],$url,'matchValue='.($um->matchValue ? 'true' : 'false'));

				$um=new CUrlManager;
				$um->urlFormat='path';
				$um->rules=$rules;
				$um->matchValue=$matchValue!=0;
				$um->init($app);
				$um->showScriptName=false;
				$url=$um->createUrl($entry['route'],$entry['params']);
				$this->assertEquals($entry['url2'],$url,'matchValue='.($um->matchValue ? 'true' : 'false'));

				$um->urlSuffix='.html';
				$url=$um->createUrl($entry['route'],$entry['params']);
				$this->assertEquals($entry['url3'],$url,'matchValue='.($um->matchValue ? 'true' : 'false'));
			}
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
		$app=new TestApplication($config);
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
		$app=new TestApplication($config);
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

	public function testDefaultParams()
	{
		$config=array(
			'basePath'=>dirname(__FILE__),
			'components'=>array(
				'request'=>array(
					'class'=>'TestHttpRequest',
				),
			),
		);
		$app=new TestApplication($config);

		$app->request->baseUrl=null; // reset so that it can be determined based on scriptUrl
		$app->request->scriptUrl='/apps/index.php';
		$um=new CUrlManager;
		$um->urlFormat='path';
		$um->rules=array(
			''=>array('site/page', 'defaultParams'=>array('view'=>'about')),
			'posts'=>array('post/index', 'defaultParams'=>array('page'=>1)),
			'<slug:[0-9a-z-]+>' => array('news/list', 'defaultParams' => array('page' => 1)),
		);
		$um->init($app);

		$url=$um->createUrl('site/page',array('view'=>'about'));
		$this->assertEquals('/apps/index.php/',$url);
		$app->request->pathInfo='';
		$_GET=array();
		$route=$um->parseUrl($app->request);
		$this->assertEquals('site/page',$route);
		$this->assertEquals(array('view'=>'about'),$_GET);

		$url=$um->createUrl('post/index',array('page'=>1));
		$this->assertEquals('/apps/index.php/posts',$url);
		$app->request->pathInfo='posts';
		$_GET=array();
		$route=$um->parseUrl($app->request);
		$this->assertEquals('post/index',$route);
		$this->assertEquals(array('page'=>'1'),$_GET);

		$url=$um->createUrl('news/list', array('slug' => 'example', 'page' => 1));
		$this->assertEquals('/apps/index.php/example',$url);
		$app->request->pathInfo='example';
		$_GET=array();
		$route=$um->parseUrl($app->request);
		$this->assertEquals('news/list',$route);
		$this->assertEquals(array('slug'=>'example', 'page'=>'1'),$_GET);
	}

	public function testVerb()
	{
		$config=array(
			'basePath'=>dirname(__FILE__),
			'components'=>array(
				'request'=>array(
					'class'=>'TestHttpRequest',
				),
			),
		);
		$rules=array(
			'article/<id:\d+>'=>array('article/read', 'verb'=>'GET'),
			'article/update/<id:\d+>'=>array('article/update', 'verb'=>'POST'),
			'article/update/*'=>'article/admin',
		);

		$entries=array(
			array(
				'scriptUrl'=>'/apps/index.php',
				'url'=>'article/123',
				'verb'=>'GET',
				'route'=>'article/read',
			),
			array(
				'scriptUrl'=>'/apps/index.php',
				'url'=>'article/update/123',
				'verb'=>'POST',
				'route'=>'article/update',
			),
			array(
				'scriptUrl'=>'/apps/index.php',
				'url'=>'article/update/123',
				'verb'=>'GET',
				'route'=>'article/admin',
			),
		);

		foreach($entries as $entry)
		{
			$_SERVER['REQUEST_METHOD']=$entry['verb'];
			$app=new TestApplication($config);
			$app->request->baseUrl=null; // reset so that it can be determined based on scriptUrl
			$app->request->scriptUrl=$entry['scriptUrl'];
			$app->request->pathInfo=$entry['url'];
			$um=new CUrlManager;
			$um->urlFormat='path';
			$um->rules=$rules;
			$um->init($app);
			$route=$um->parseUrl($app->request);
			$this->assertEquals($entry['route'],$route);
		}
	}

	public function testParsingOnly()
	{
		$config=array(
			'basePath'=>dirname(__FILE__),
			'components'=>array(
				'request'=>array(
					'class'=>'TestHttpRequest',
				),
			),
		);
		$rules=array(
			'(articles|article)/<id:\d+>'=>array('article/read', 'parsingOnly'=>true),
			'article/<id:\d+>'=>array('article/read', 'verb'=>'GET'),
		);

		$_SERVER['REQUEST_METHOD']='GET';
		$app=new TestApplication($config);
		$app->request->baseUrl=null; // reset so that it can be determined based on scriptUrl
		$app->request->scriptUrl='/apps/index.php';
		$app->request->pathInfo='articles/123';
		$um=new CUrlManager;
		$um->urlFormat='path';
		$um->rules=$rules;
		$um->init($app);

		$route=$um->parseUrl($app->request);
		$this->assertEquals('article/read',$route);

		$url=$um->createUrl('article/read', array('id'=>345));
		$this->assertEquals('/apps/index.php/article/345',$url);
	}
}
