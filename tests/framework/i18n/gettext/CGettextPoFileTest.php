<?php

class CGettextPoFileTest extends CTestCase
{
	public function testLoad()
	{
		$poFile=new CGettextPoFile();
		$poFilePath=dirname(__FILE__).'/../data/ru/test.po';
		$context1=$poFile->load($poFilePath,'context1');
		$context2=$poFile->load($poFilePath,'context2');

		// item count
		$this->assertCount(4,$context1);
		$this->assertCount(2,$context2);

		// original messages
		$this->assertArrayHasKey("Missing\n\r\t\"translation.",$context1);
		$this->assertArrayHasKey("Aliquam tempus elit vel purus molestie placerat. In sollicitudin tincidunt\naliquet. Integer tincidunt gravida tempor. In convallis blandit dui vel malesuada.\nNunc vel sapien nunc, a pretium nulla.",$context1);
		$this->assertArrayHasKey("String number two.",$context1);
		$this->assertArrayHasKey("Nunc vel sapien nunc, a pretium nulla.\nPellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.",$context1);

		$this->assertArrayHasKey("\n\nThe other context.\n",$context2);
		$this->assertArrayHasKey("test1\\ntest2\n\\\ntest3",$context2);

		// translated messages
		$this->assertTrue(in_array("\n\r\t\"",$context1));
		$this->assertTrue(in_array("Олицетворение однократно. Представленный лексико-семантический анализ является\nпсихолингвистическим в своей основе, но механизм сочленений полидисперсен. Впечатление\nоднократно. Различное расположение выбирает сюжетный механизм сочленений.",$context1));
		$this->assertTrue(in_array('Строка номер два.',$context1));
		$this->assertTrue(in_array('Короткий перевод.',$context1));

		$this->assertTrue(in_array("\n\nДругой контекст.\n",$context2));
		$this->assertSame("тест1\\nтест2\n\\\nтест3",$context2["test1\\ntest2\n\\\ntest3"]);
	}

	private $_file;
	private $_backtrackLimit;

	public function setUp()
	{
		parent::setUp();
		$this->_file=tempnam(sys_get_temp_dir(),'yii-po-');
		$this->_backtrackLimit=ini_get('pcre.backtrack_limit');
	}

	public function tearDown()
	{
		ini_set('pcre.backtrack_limit',$this->_backtrackLimit);
		unlink($this->_file);
		parent::tearDown();
	}

	/**
	 * @dataProvider contextProvider
	 */
	public function testSpecialCharacterRoundTrip($context)
	{
		$expected=array(
			'before'=>'First translation',
			'back\slash'=>'back\slash',
			'trailing\\'=>'trailing\\',
			'literal\n\r\t'=>'C:\temp\file "q"\nend',
			'unknown\w\b'=>'unknown\w\b',
			'quote" '=>'quote" ',
			"new\nline\r\ttab"=>"new\nline\r\ttab",
			'empty'=>'',
			'after'=>'Last translation',
		);
		$messages=array();
		foreach($expected as $id=>$translation)
			$messages[$context.chr(4).$id]=$translation;
		// Also verify an entry without msgctxt and exclude unrelated contexts.
		$messages['plain']='Unscoped translation';
		$messages['other'.chr(4).'unrelated']='Other translation';
		if($context==='')
			$expected['plain']='Unscoped translation';

		$poFile=new CGettextPoFile();
		$poFile->save($this->_file,$messages);
		$this->assertSame($expected,$poFile->load($this->_file,$context));
		$this->assertSame(array('unrelated'=>'Other translation'),$poFile->load($this->_file,'other'));
	}

	public function contextProvider()
	{
		return array(
			array(''),
			array('round\trip"ctx'),
			array("context\n\r\t\\"),
		);
	}

	public function testSaveUsesPoEscapes()
	{
		$poFile=new CGettextPoFile();
		$poFile->save($this->_file,array("path\\\"\n".chr(4)."source\\"=>"value\\\"\n\r\t"));
		$expected=<<<'PO'
msgctxt "path\\\"\n"
msgid "source\\"
msgstr "value\\\"\n\r\t"
PO;
		$this->assertSame(str_replace("\r\n","\n",$expected)."\n\n",file_get_contents($this->_file));
	}

	/**
	 * @dataProvider longStringsProvider
	 */
	public function testLongStringRoundTrip($context,$id,$translation)
	{
		$expected=array('before'=>'First translation',$id=>$translation,'after'=>'Last translation');
		$messages=array();
		foreach($expected as $source=>$message)
			$messages[$context.chr(4).$source]=$message;

		$poFile=new CGettextPoFile();
		$poFile->save($this->_file,$messages);
		$this->assertSame($expected,$poFile->load($this->_file,$context));
	}

	public function longStringsProvider()
	{
		$plain=str_repeat('a',10000);
		$escaped=str_repeat("a\\\"\n\r\t",2000);
		return array(
			array('context','source',$plain),
			array('context',$plain,'translation'),
			array($plain,'source','translation'),
			array('context','source',$escaped),
			array('context',$escaped,'translation'),
			array($escaped,'source','translation'),
		);
	}

	public function testLoadQuotedFragments()
	{
		$content=<<<'PO'
msgctxt "con"
"text\\\" "
msgid "source\" "
"\\n\w"
msgstr "translation\" "
"\\n\b"

msgid "plain"
msgstr "unscoped"
PO;
		file_put_contents($this->_file,$content."\n");
		$poFile=new CGettextPoFile();
		$this->assertSame(array('source" \\n\w'=>'translation" \\n\b'),$poFile->load($this->_file,'context\" '));
		$this->assertSame(array('plain'=>'unscoped'),$poFile->load($this->_file,''));
	}

	public function testManyContinuationLines()
	{
		$fragments=str_repeat('"a\n"'."\n",10000);
		$content='msgid "before"'."\n".'msgstr "First translation"'."\n\n"
			.'msgid "source"'."\n".'msgstr ""'."\n".$fragments."\n"
			.'msgid "after"'."\n".'msgstr "Last translation"'."\n";
		file_put_contents($this->_file,$content);
		$poFile=new CGettextPoFile();
		$this->assertSame(array(
			'before'=>'First translation',
			'source'=>str_repeat("a\n",10000),
			'after'=>'Last translation',
		),$poFile->load($this->_file,''));
	}

	public function testMalformedEntryDoesNotConsumeNextEntry()
	{
		$content=<<<'PO'
msgid "before"
msgstr "First translation"

msgid "broken"
msgstr "unterminated\"

msgid "after"
msgstr "Last translation"
PO;
		file_put_contents($this->_file,$content."\n");
		$poFile=new CGettextPoFile();
		$this->assertSame(array('before'=>'First translation','after'=>'Last translation'),$poFile->load($this->_file,''));
	}

	public function testLoadReportsRegexErrors()
	{
		file_put_contents($this->_file,"msgid \"source\"\nmsgstr \"translation\"\n");
		ini_set('pcre.backtrack_limit','0');
		$this->setExpectedException('CException','Unable to parse PO file');
		$poFile=new CGettextPoFile();
		$poFile->load($this->_file,'');
	}

	public function testDecodeReportsRegexErrors()
	{
		$decode=new ReflectionMethod('CGettextPoFile','decode');
		$decode->setAccessible(true);
		ini_set('pcre.backtrack_limit','0');
		$this->setExpectedException('CException','Unable to decode PO string');
		$decode->invoke(new CGettextPoFile(),'"translation"');
	}
}
