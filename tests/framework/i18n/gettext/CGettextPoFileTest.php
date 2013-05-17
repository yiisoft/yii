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
		$this->assertArrayHasKey("test1\\\ntest2\n\\\\\ntest3",$context2);

		// translated messages
		$this->assertTrue(in_array("\n\r\t\"",$context1));
		$this->assertTrue(in_array("Олицетворение однократно. Представленный лексико-семантический анализ является\nпсихолингвистическим в своей основе, но механизм сочленений полидисперсен. Впечатление\nоднократно. Различное расположение выбирает сюжетный механизм сочленений.",$context1));
		$this->assertTrue(in_array('Строка номер два.',$context1));
		$this->assertTrue(in_array('Короткий перевод.',$context1));

		$this->assertTrue(in_array("\n\nДругой контекст.\n",$context2));
		$this->assertTrue(in_array("тест1\\\nтест2\n\\\\\nтест3",$context2));
	}
}
