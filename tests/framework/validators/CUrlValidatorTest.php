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

	public function testArbitraryUrl()
	{
		$urlValidator = new CUrlValidator();
		$url = 'http://testing-arbitrary-domain.com/';
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
			array('https://www.yiiframework.com/extensions/', true, 'https://www.yiiframework.com/extensions/'),
			array('ftp://www.yiiframework.com/', true, false),
			array('www.yiiframework.com', true, false),

			// IDN validation disabled
			array('http://президент.рф/', false, false),
			array('http://bücher.de/?get=param', false, false),
			array('http://检查域.cn/', false, false),
			array('http://mañana.com/', false, false),
			array('http://☃-⌘.com/', false, false),
			array('http://google.com/', false, 'http://google.com/'),
			array('http://www.yiiframework.com/forum/', false, 'http://www.yiiframework.com/forum/'),
			array('https://www.yiiframework.com/extensions/', false, 'https://www.yiiframework.com/extensions/'),
			array('ftp://www.yiiframework.com/', false, false),
			array('www.yiiframework.com', false, false),
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

	public function providerValidSchemes()
	{
		return array(
			array('ftp://yiiframework.com/', array('ftp', 'http', 'https'), 'ftp://yiiframework.com/'),
			array('ftp://yiiframework.com/', array('http', 'https'), false),
			array('ftp://yiiframework.com/', array('ftp'), 'ftp://yiiframework.com/'),

			array('that-s-not-an-url-at-all', array('ftp', 'http', 'https'), false),
			array('that-s-not-an-url-at-all', array(), false),
			array('ftp://that-s-not-an-url-at-all', array('ftp'), false),

			array('http://☹.com/', array('ftp'), false),
			array('http://☹.com/', array('rsync'), false),
			array('http://☹.com/', array('http', 'https'), false),

			array('rsync://gentoo.org:873/distfiles/', array('rsync', 'http', 'https'), 'rsync://gentoo.org:873/distfiles/'),
			array('rsync://gentoo.org:873/distfiles/', array('http', 'https'), false),
			array('rsync://gentoo.org:873/distfiles/', array('rsync'), 'rsync://gentoo.org:873/distfiles/'),
		);
	}

	/**
	 * @dataProvider providerValidSchemes
	 *
	 * @param string $url
	 * @param array $validSchemes
	 * @param string $assertion
	 */
	public function testValidSchemes($url, $validSchemes, $assertion)
	{
		$urlValidator = new CUrlValidator();
		$urlValidator->validSchemes = $validSchemes;
		$result = $urlValidator->validateValue($url);
		$this->assertEquals($assertion, $result);
	}

	public function providerDefaultScheme()
	{
		return array(
			array('https://yiiframework.com/?get=param', null, 'https://yiiframework.com/?get=param'),
			array('ftp://yiiframework.com/?get=param', null, false),
			array('yiiframework.com/?get=param', null, false),
			array('that-s-not-an-url-at-all', null, false),

			array('https://yiiframework.com/?get=param', 'http', 'https://yiiframework.com/?get=param'),
			array('ftp://yiiframework.com/?get=param', 'http', false),
			array('yiiframework.com/?get=param', 'http', 'http://yiiframework.com/?get=param'),
			array('that-s-not-an-url-at-all', 'http', false),

			array('https://yiiframework.com/?get=param', 'ftp', 'https://yiiframework.com/?get=param'),
			array('ftp://yiiframework.com/?get=param', 'ftp', false),
			array('yiiframework.com/?get=param', 'ftp', false),
			array('that-s-not-an-url-at-all', 'ftp', false),
		);
	}

	/**
	 * @dataProvider providerDefaultScheme
	 *
	 * @param string $url
	 * @param array $defaultScheme
	 * @param string $assertion
	 */
	public function testDefaultScheme($url, $defaultScheme, $assertion)
	{
		$urlValidator = new CUrlValidator();
		$urlValidator->defaultScheme = $defaultScheme;
		$result = $urlValidator->validateValue($url);
		$this->assertEquals($assertion, $result);
	}


	public function providerAllowEmpty()
	{
		return array(
			array('https://yiiframework.com/?get=param', false, 'https://yiiframework.com/?get=param'),
			array('ftp://yiiframework.com/?get=param', false, false),
			array('yiiframework.com/?get=param', false, false),
			array('that-s-not-an-url-at-all', false, false),
			array('http://☹.com/', false, false),
			array('rsync://gentoo.org:873/distfiles/', false, false),
			array('https://gentoo.org:8080/distfiles/', false, 'https://gentoo.org:8080/distfiles/'),
			array(' ', false, false),
			array('', false, false),

			array('https://yiiframework.com/?get=param', true, 'https://yiiframework.com/?get=param'),
			array('ftp://yiiframework.com/?get=param', true, false),
			array('yiiframework.com/?get=param', true, false),
			array('that-s-not-an-url-at-all', true, false),
			array('http://☹.com/', true, false),
			array('rsync://gentoo.org:873/distfiles/', true, false),
			array('https://gentoo.org:8080/distfiles/', true, 'https://gentoo.org:8080/distfiles/'),
			array(' ', true, false),
			array('', true, ''),
		);
	}

	/**
	 * @dataProvider providerAllowEmpty
	 *
	 * @param string $url
	 * @param array $allowEmpty
	 * @param string $assertion
	 */
	public function testAllowEmpty($url, $allowEmpty, $assertion)
	{
		$urlValidator = new CUrlValidator();
		$urlValidator->allowEmpty = $allowEmpty;
		$result = $urlValidator->validateValue($url);
		$this->assertEquals($assertion, $result);
	}

	/**
	 * https://github.com/yiisoft/yii/issues/1955
	 */
	public function testArrayValue()
	{
		$model=new ValidatorTestModel('CUrlValidatorTest');
		$model->url=array('http://yiiframework.com/');
		$model->validate(array('url'));
		$this->assertTrue($model->hasErrors('url'));
		$this->assertEquals(array('Url is not a valid URL.'),$model->getErrors('url'));
	}
}
