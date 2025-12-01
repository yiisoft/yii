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
    
    public function testEncodeUnicodeBeyondBMP()
	{
		$expression=CJavaScript::encode("\xF0\x90\x80\x80",true);
		$this->assertEquals("'\\uD800\\uDC00'",$expression);
	}

	public function testEncode()
	{
		$expression=CJavaScript::encode(new CJavaScriptExpression("function() { /* callback */ }"));
		$this->assertEquals("function() { /* callback */ }",$expression);
	}

	private function getUnicodeTestString()
	{
		$unicodeChar1 = json_decode('"'.'\u2028'.'"');
		$unicodeChar2 = json_decode('"'.'\u2029'.'"');
		return "test {$unicodeChar1}\ntest $unicodeChar2";
	}

	public function testQuote()
	{
		$input=$this->getUnicodeTestString();
		$output=CJavaScript::quote($input);
		$this->assertEquals('test\x20\u2028\x0Atest\x20\u2029',$output);
	}

	public function testQuoteForUrl()
	{
		$input=$this->getUnicodeTestString();
		$output=CJavaScript::quote($input,true);
		$this->assertEquals('test%20%E2%80%A8%0Atest%20%E2%80%A9',$output);
	}

	public function testQuoteWithNull()
	{
		$input=null;
		$output=CJavaScript::quote($input);
		$this->assertSame('',$output);
	}

}
