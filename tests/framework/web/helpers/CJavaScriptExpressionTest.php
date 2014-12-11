<?php
/**
 * CJavaScriptExpressionTest
 */
class CJavaScriptExpressionTest extends CTestCase
{
	public function testToString()
	{
		$expression=new CJavaScriptExpression("function(){return \"Hello, world!\";}");
		$this->assertEquals('function(){return "Hello, world!";}', $expression);
	}
}