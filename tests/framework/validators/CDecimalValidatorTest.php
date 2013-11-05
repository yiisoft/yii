<?php

class CDecimalValidatorTest extends CTestCase {

    private static $jsTest = '';

    public function testValidation() {
        $m = new CDecimalValidator();
        $basicError = strtr($m->message, array('{attribute}' => 'Decimal'));
        $intError = strtr($m->tooManyIntDigits, array('{attribute}' => 'Decimal', '{max}' => '{iMax}'));
        $floatError = strtr($m->tooManyFloatDigits, array('{attribute}' => 'Decimal', '{max}' => '{fMax}'));
        
        $cases = array(
            // empty inputs
            array(true, 1, 1, null),
            array(false, 1, 1, null, $basicError),
            array(true, 1, 1, ''),
            array(false, 1, 1, '', $basicError),
            array(false, 1, 1, '0'),
            array(false, 1, 1, '0.0'),
            
            // non-numeric inputs
            array(false, 1, 1, array(), $basicError),
            array(false, 1, 1, new stdClass(), $basicError),
            array(false, 1, 1, 'abcde', $basicError),
            
            // numeric input in invalid formats
            array(false, 1, 1, '0e4', $basicError),
            array(false, 1, 1, '0x4', $basicError),
            
            // valid formats invalidated by extra garbage
            array(true, 1, 1, '0.0e4', $basicError),
            
            // valid formats with leading/trailing zeroes and punctuation
            array(false, 1, 1, '-00.00'),
            array(false, 1, 1, '+01.10'),
            array(false, 1, 1, '-00.00'),
            
            // valid inputs outside range limits
            array(true, 1, 1, '11.1', $intError),
            array(true, 1, 1, '1.11', $floatError),
            array(true, 1, 1, '11.11', array($intError, $floatError)),
            
            // valid inputs
            array(false, 1, 1, 0),
            array(false, 1, 1, 0.0),
            array(false, 1, 1, 9),
            array(false, 1, 1, 9.9),
            array(false, 1, 1, '9'),
            array(false, 1, 1, '9.9'),
            array(false, 1, 0, 1.0),
            array(false, 0, 1, 0.1),
            array(false, 0, 1, '0.1'),
            array(false, 0, 1, '.1'),
            array(false, 7, 5, '1,111,111.11111'),
            array(false, 7, 5, '-1,111,111.11111'),
            array(false, 7, 5, '+1,111,111.11111'),
            array(false, 7, 5, '+01,111,111.11111'),
        );
        
        $i = 1;
        foreach($cases as $case) {
            $this->inputTest($i++, $case[0], $case[1], $case[2], $case[3], @$case[4]);
        }
    }
    
    private function inputTest($testNum, $allowEmpty, $iCount, $fCount, $input, $errors = null) {
        
        $model = new CDecimalValidatorTestModel();
        $model->decimal = $input;
        
        $validator = new CDecimalValidator();
        $validator->maxIntDigits = $iCount;
        $validator->maxFloatDigits = $fCount;
        $validator->attributes = array('decimal');
        $validator->allowEmpty = $allowEmpty;
        $validator->validate($model);
        
        if(empty($errors)) {
            $errors = array();
        } else {
            if(!is_array($errors)) {
                $errors = array($errors);
            }
            foreach($errors as $key => $value) {
                $errors[$key] = strtr($value, array('{iMax}' => $iCount, '{fMax}' => $fCount));
            }
        }
        $dErrors = empty($errors) ? array() : array('decimal' => $errors);
        $this->assertEquals($dErrors, $model->getErrors(), "case $testNum:");
        
        // TODO: run generated js per test for each one assert messages == (array)$errors['decimal]
        $input = json_encode($input);
        self::$jsTest .= "\nconsole.log('case '+$testNum);\nmessages = [];\nvalue = $input;\n";
        self::$jsTest .= $validator->clientValidateAttribute($model, 'decimal');
        self::$jsTest .= "\nconsole.log(".CJSON::encode($errors).");\nconsole.log(messages);\n";
    }
    
    public static function tearDownAfterClass() {
        file_put_contents('./jsTest.js', self::$jsTest);
    }

}

class CDecimalValidatorTestModel extends CFormModel {

    public $decimal;
    private $fieldLabels = array('decimal' => 'Decimal');

    public function attributeNames() {
        return array_keys($this->fieldLabels);
    }
    
    public function attributeLabels() {
        return $this->fieldLabels;
    } 
}