<?php

require_once 'ValidatorTestModel.php';

class CFilterValidatorTest extends CTestCase
{
	public function filterMethodDataProvider() {
        return array(
            'trim whitespaces' => array('  value  ', 'value'),
            'trim tabs and linebreaks #2' => array(' 
            value   
            ', 'value'),
            'trim nothing' => array('value', 'value'),
            'trim null' => array(null, null),
            'trim empty string' => array('', ''),
        );
    }

    /**
     * @dataProvider filterMethodDataProvider
     */
	public function testValidate($value, $expected) {
        $model = new ValidatorTestModel('CFilterValidatorTest');
        $model->string1 = $value;
        $model->validate(array('string1'));
        $this->assertEquals($expected, $model->string1);
    }
}
