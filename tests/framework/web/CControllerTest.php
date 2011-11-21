<?php

Yii::import('system.web.CController');
Yii::import('system.web.filters.CFilter');
Yii::import('system.web.actions.CAction');

class TestController extends CController
{
	public $defaultAction='external';
	public $internal=0;
	public $external=0;
	public $internalFilter1=0;
	public $internalFilter2=0;
	public $internalFilter3=0;
	public $externalFilter=0;

	public function filters()
	{
		return array(
			'filter1',
			'filter2 + internal',
			array(
				'TestFilter',
				'expire'=>300,
			),
			'filter3 - internal',
		);
	}

	public function actions()
	{
		return array(
			'external'=>'TestAction',
		);
	}

	public function missingAction($actionName)
	{
		throw new CException('test missing');
	}

	public function filterFilter1($chain)
	{
		$chain->run();
		$this->internalFilter1++;
	}

	public function filterFilter2($chain)
	{
		$this->internalFilter2++;
		$chain->run();
	}

	public function filterFilter3($chain)
	{
		$this->internalFilter3++;
		$chain->run();
	}

	public function actionInternal()
	{
		$this->internal++;
	}

	public $a;
	public $b;
	public $c;
	public $d;

	public function actionCreate($a,$b,$c=3,$d=4)
	{
		$this->a=$a;
		$this->b=$b;
		$this->c=$c;
		$this->d=$d;
	}
}

class TestFilter extends CFilter
{
	public $expire=0;
	public function filter($chain)
	{
		if($chain->controller->externalFilter<=1)
		{
			$chain->controller->externalFilter++;
			$chain->run();
		}
	}
}

class TestAction extends CAction
{
	public function run()
	{
		$this->controller->external++;
	}
}

class CControllerTest extends CTestCase
{
	public function testDefaultProperties()
	{
		$app=new TestApplication;
		$_SERVER['REQUEST_METHOD']='GET';
		$c=new CController('test/subtest');
		$this->assertEquals($c->id,'test/subtest');
		$this->assertEquals($c->filters(),array());
		$this->assertEquals($c->actions(),array());
		$this->assertNull($c->action);
		$this->assertEquals($c->defaultAction,'index');
		$this->assertEquals($c->viewPath,$app->viewPath.DIRECTORY_SEPARATOR.'test/subtest');
		$this->setExpectedException('CHttpException');
		$c->missingAction('index');
	}

	public function testRunAction()
	{
		$app=new TestApplication;
		$c=new TestController('test');
		$this->assertEquals($c->internal,0);
		$this->assertEquals($c->external,0);
		$this->assertEquals($c->internalFilter1,0);
		$this->assertEquals($c->internalFilter2,0);
		$this->assertEquals($c->internalFilter3,0);
		$this->assertEquals($c->externalFilter,0);

		$c->run('');
		$this->assertEquals($c->internal,0);
		$this->assertEquals($c->external,1);
		$this->assertEquals($c->internalFilter1,1);
		$this->assertEquals($c->internalFilter2,0);
		$this->assertEquals($c->internalFilter3,1);
		$this->assertEquals($c->externalFilter,1);

		$c->run('internal');
		$this->assertEquals($c->internal,1);
		$this->assertEquals($c->external,1);
		$this->assertEquals($c->internalFilter1,2);
		$this->assertEquals($c->internalFilter2,1);
		$this->assertEquals($c->internalFilter3,1);
		$this->assertEquals($c->externalFilter,2);

		$c->run('external');
		$this->assertEquals($c->internal,1);
		$this->assertEquals($c->external,1);
		$this->assertEquals($c->internalFilter1,3);
		$this->assertEquals($c->internalFilter2,1);
		$this->assertEquals($c->internalFilter3,1);
		$this->assertEquals($c->externalFilter,2);

		$this->setExpectedException('CException');
		$c->run('unknown');
	}

	public function testActionParams()
	{
		$app=new TestApplication;
		$c=new TestController('test');

		$_GET['a']=1;
		$_GET['b']='2';
		$c->run('create');
		$this->assertTrue($c->a===1);
		$this->assertTrue($c->b==='2');
		$this->assertTrue($c->c===3);
		$this->assertTrue($c->d===4);

		$_GET['a']=11;
		$_GET['b']='22';
		$_GET['d']='44';
		$c->run('create');
		$this->assertTrue($c->a===11);
		$this->assertTrue($c->b==='22');
		$this->assertTrue($c->c===3);
		$this->assertTrue($c->d==='44');
	}

	public function testActionParamsInvalid()
	{
		$app=new TestApplication;
		$c=new TestController('test');
		$_GET=array('a'=>1);
		$this->setExpectedException('CException');
		$c->run('create');
	}
}
