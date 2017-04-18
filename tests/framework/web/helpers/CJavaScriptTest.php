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
		$this->assertEquals("'js\\x3Afunction\\x28\\x29\\x20\\x7B\\x20\\x2F\\x2A\\x20callback\\x20\\x2A\\x2F\\x20\\x7D'",$expression);
	}

    public function testEncode()
    {
        $expression=CJavaScript::encode(new CJavaScriptExpression("function() { /* callback */ }"));
        $this->assertEquals("function() { /* callback */ }",$expression);
    }

    public function testQuote()
    {
        $input='ro cks!
            test';
        $output=CJavaScript::quote($input);
        $this->assertEquals('ro\u2028cks\x21\x0D\x0A\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20test',$output);
    }

    public function testQuoteForUrl()
    {
        $input='ro cks!
            test';
        $output=CJavaScript::quote($input,true);
        $this->assertEquals('ro%E2%80%A8cks%21%0D%0A%20%20%20%20%20%20%20%20%20%20%20%20test',$output);
    }
}
