<?php

class CSanitizerTest extends CTestCase
{
    /**
     * @var CModel
     */
    private $model;
    public function setUp()
    {
        $this->model = new SanitizeTestModel();
        $this->model->foo = "   Some String    ";
        $this->model->bar = "\nRemove Only Left Newline\n";
        $this->model->foobar = "some value";
    }
    /**
     * 
     * @covers CModel::sanitize
     */
    public function testSanitize()
    {
        $this->model->sanitize();
        $this->assertEquals("Some String", $this->model->foo);
        $this->assertEquals("Remove Only Left Newline\n", $this->model->bar);
        $this->assertEquals("succeeded", $this->model->foobar);
    }
	
	public function testScenarios()
	{
		$model = new SanitizerScenariosTestModel();
		$testmodel = clone $model;
		//scenario 1
		$testmodel->setScenario('scenario1');
		$testmodel->sanitize();
		$this->assertEquals('title', $testmodel->title);
		$this->assertEquals('firstName', $testmodel->firstName);
		$this->assertEquals("\nbirthday", $testmodel->birthday);
		$testmodel = clone $model;
		// scenario 3
		$testmodel->setScenario('scenario3');
		$testmodel->sanitize();
		$this->assertEquals("\nfirstName", $testmodel->firstName);
		$this->assertEquals('nickName', $testmodel->nickName);
		
	}
}

class SanitizeTestModel extends CFormModel
{
    public $foo;
    public $bar;
    public $foobar;
    public $barfoo;
    
    public function sanitizationRules()
    {
        return array(
            array('foo', 'trim'),
            array('bar', 'trim', 'mode'=>'ltrim'),
            array('foobar', 'sanatizeFooBar'),
        );
    }
    
    public function sanatizeFooBar($attribute, $params)
    {
        $this->foobar = 'succeeded';
        return true;
    }
}

class SanitizerScenariosTestModel extends CFormModel
{
	public $title = "\ntitle";
	public $firstName = "\nfirstName";
	public $lastName = "\nlastName";
	public $patronymic = "\npatronymic";
	public $nickName = "\nnickName";

	public $login = "\nlogin";
	public $password = "\npassword";

	public $birthday = "\nbirthday";

	public function sanitizationRules()
	{
		return array(
			// scenario1
			array('title', 'trim', 'on'=>'scenario1'),

			// scenario1 and scenario2
			array('firstName', 'trim', 'except'=>'scenario3, scenario4'),

			// scenario1, scenario2 and scenario3
			array('lastName', 'trim', 'on'=>array('scenario1', 'scenario2', 'scenario3')),

			// scenario1, scenario2 and scenario3
			array('patronymic', 'trim', 'except'=>array('scenario4')),

			// scenario1 and scenario3
			array('nickName', 'trim', 'on'=>array('scenario1', 'scenario2', 'scenario3'), 'except'=>'scenario2'),

			// scenario1, scenario2, scenario3 and scenario4
			array('login', 'trim'),

			// useless rule
			array('password', 'trim', 'on'=>'scenario1,scenario2,scenario3,scenario4',
				'except'=>array('scenario1', 'scenario2', 'scenario3', 'scenario4')),

			// scenario2
			array('birthday', 'trim', 'on'=>'scenario2', 'except'=>'scenario3'),
		);
	}
}
