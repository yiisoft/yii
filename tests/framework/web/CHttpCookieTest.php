<?php

class CHttpCookieTest extends CTestCase
{
	/**
	 * @covers CHttpCookie::configure
	 * @covers CHttpCookie::__construct
	 */
	public function testConfigure()
	{
		//covers construct
		$cookie=new CHttpCookie('name','value');
		$this->assertEquals('name',$cookie->name,'Constructor failure. Name should have been set there');
		$this->assertEquals('value',$cookie->value,'Constructor failure. Value should have been set there');
		$this->assertEquals('',$cookie->domain,'Default value for CHttpCookie::$domain has been touched');
		$this->assertEquals(0,$cookie->expire,'Default value for CHttpCookie::$expire has been touched');
		$this->assertEquals('/',$cookie->path,'Default value for CHttpCookie::$path has been touched');
		$this->assertFalse($cookie->secure,'Default value for CHttpCookie::$secure has been touched');
		$this->assertFalse($cookie->httpOnly,'Default value for CHttpCookie::$httpOnly has been touched');
		$options=array(
			'expire'=>123123,
			'httpOnly'=>true,
		);
		// create cookie with options
		$cookie2=new CHttpCookie('name2','value2',$options);
		$this->assertEquals($options['expire'],$cookie2->expire,'Configure inside the Constructor has been failed');
		$this->assertEquals($options['httpOnly'],$cookie2->httpOnly,'Configure inside the Constructor has been failed');
		//configure afterwards
		$cookie->configure($options);		
		$this->assertEquals($options['expire'],$cookie->expire);
		$this->assertEquals($options['httpOnly'],$cookie->httpOnly);
		// Set name and value via configure (should have no effect)
		$name=$cookie->name;
		$cookie->configure(array('name'=>'someNewName'));
		$this->assertEquals($name,$cookie->name);
		$value=$cookie->value;
		$cookie->configure(array('value'=>'someNewValue'));
		$this->assertEquals($value,$cookie->value);
		//new configure should not override already set configuration
		$this->assertEquals($options['httpOnly'],$cookie->httpOnly);
	}
	/**
	 * @covers CHttpCookie::__toString
	 */
	public function test__ToString()
	{
		$cookie=new CHttpCookie('name','someValue');
		// Note on http://www.php.net/manual/en/language.oop5.magic.php#object.tostring
		ob_start();
		echo $cookie;
		$this->assertEquals(ob_get_clean(),$cookie->value);
		if(version_compare(PHP_VERSION,'5.2','>='))
		{
			$this->assertEquals($cookie->value,(string)$cookie);
		}
	}
}