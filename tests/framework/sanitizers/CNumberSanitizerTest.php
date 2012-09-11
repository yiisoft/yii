<?php

class CNumberSanitizerTest extends CTestCase
{
    public function getTestModel()
	{
		$model = new SanitizeNumberTestModel;
		$model->int = 45.4231;
		$model->uint = -234.12;
		$model->float = "456.33";
		$model->ufloat = "-65.466434";
		$model->intNotBelowZero = "-4545.34";
		return $model;
	}
	
	public function testSanitizeAttributes()
	{
		$model = $this->getTestModel();
		$model->sanitize();
		$this->assertEquals(45, $model->int);
		$this->assertTrue(is_int($model->int));
		$this->assertEquals(234, $model->uint);
		$this->assertTrue(is_int($model->uint));
		$this->assertEquals(430.23, $model->float);
		$this->assertTrue(is_float($model->float));
		$this->assertEquals(65.47, $model->ufloat);
		$this->assertTrue(is_float($model->ufloat));
		$this->assertEquals(0, $model->intNotBelowZero);
		$this->assertEquals(45, $model->emptyValue);
	}
}

class SanitizeNumberTestModel extends CFormModel
{
    public $int;
    public $uint;
	public $float;
    public $ufloat;
    public $intNotBelowZero;
	public $emptyValue;
    
    public function sanitizationRules()
    {
        return array(
            array('int', 'number', 'to'=>'int'),
            array('uint', 'number', 'to'=>'uint'),
			array('float', 'number', 'to'=>'float', 'max'=>430.23),
			array('ufloat', 'number', 'to'=>'ufloat', 'precision'=>2),
			array('intNotBelowZero', 'number', 'min'=>0),
			array('emptyValue', 'number', 'allowEmpty'=>false, 'emptyValue'=>45),
        );
    }
}
