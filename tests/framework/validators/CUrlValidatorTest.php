<?php
require_once('ValidatorTestModel.php');

class CUrlValidatorTest extends CTestCase
{
	public function testEmpty()
	{
		$model = new ValidatorTestModel('CUrlValidatorTest');
		$model->validate(array('url'));
		$this->assertArrayHasKey('url', $model->getErrors());
	}

	public function testArbitaryUrl()
	{
		$urlValidator = new CUrlValidator();
		$url = 'http://testing-arbitary-domain.com/';
		$result = $urlValidator->validateValue($url);
		$this->assertEquals($url, $result);
	}

	public function providerIDNUrl()
	{
		return array(
			// IDN validation enabled
			array('http://президент.рф/', true, 'http://президент.рф/'),
			array('http://bücher.de/?get=param', true, 'http://bücher.de/?get=param'),
			array('http://检查域.cn/', true, 'http://检查域.cn/'),
			array('http://mañana.com/', true, 'http://mañana.com/'),
			array('http://☃-⌘.com/', true, 'http://☃-⌘.com/'),
			array('http://google.com/', true, 'http://google.com/'),
			array('http://www.yiiframework.com/forum/', true, 'http://www.yiiframework.com/forum/'),
			// IDN validation disabled
			array('http://президент.рф/', false, false),
			array('http://bücher.de/?get=param', false, false),
			array('http://检查域.cn/', false, false),
			array('http://mañana.com/', false, false),
			array('http://☃-⌘.com/', false, false),
			array('http://google.com/', false, 'http://google.com/'),
			array('http://www.yiiframework.com/forum/', false, 'http://www.yiiframework.com/forum/'),
		);
	}

    /**
     * @dataProvider providerIDNUrl
     *
     * @param string $url
	 * @param boolean $validateIDN
	 * @param string $assertion
     */
	public function testIDNUrl($url, $validateIDN, $assertion)
	{
		$urlValidator = new CUrlValidator();
		$urlValidator->validateIDN = $validateIDN;
		$result = $urlValidator->validateValue($url);
		$this->assertEquals($assertion, $result);
	}
}
