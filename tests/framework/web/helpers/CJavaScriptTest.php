<?php
/**
 * CJavaScriptTest
 */
class CJavaScriptTest extends CTestCase
{
	public function testLegacyEncode()
	{
		$expression=CJavaScript::encode("js:function() { /* callback */ }");
		$this->assertEquals("function() { /* callback */ }",$expression);
	}

	public function testLegacyEncodeSafe()
	{
		$expression=CJavaScript::encode("js:function() { /* callback */ }",true);
		$this->assertEquals("'js:function() { /* callback */ }'",$expression);
	}

	public function testEncode()
	{
		$expression=CJavaScript::encode(new CJavaScriptExpression("function() { /* callback */ }"));
		$this->assertEquals("function() { /* callback */ }",$expression);
	}
}
