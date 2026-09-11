<?php

/**
 * @author Carsten Brandt <mail@cebe.cc>
 */
class CMarkdownParserTest extends CTestCase
{
	public function testUTF8()
	{
		// safeTransform() loads the bundled HTML Purifier, which uses short array syntax since 4.17.0
		if(version_compare(PHP_VERSION,'5.6','<'))
			$this->markTestSkipped('The bundled HTML Purifier requires PHP 5.6 or above.');

		$markdown = <<<'MARKDOWN'
~~~
[php]

$info = array();

function oGetMessage($key, $fields) {
    $messages = array(
        'EVENT_TYPE_UPDATE' => 'Тип почтового события #EVENT_NAME# [#ID#] успешно обновлён',
        'EVENT_TYPE_UPDATE_ERROR' => 'Ошибка обновления типа почтового события #EVENT_NAME# [#ID#]',
        'EVENT_TYPE_ADD' => 'Тип почтового события #EVENT_NAME# [#ID#] успешно добавлен',
        'EVENT_TYPE_ADD_ERROR' => 'Ошибка добавления типа почтового события #EVENT_NAME#',
        'EVENT_MESSAGE_UPDATE' => 'Почтовый шаблон с типом #EVENT_NAME# [#ID#] и темой «#SUBJECT#» успешно обновлён',
        'EVENT_MESSAGE_UPDATE_ERROR' => 'Ошибка обновления почтового шаблона с типом #EVENT_NAME# [#ID#] и темой «#SUBJECT#». #ERROR#',
        'EVENT_MESSAGE_ADD' => 'Почтовый шаблон с типом #EVENT_NAME# [#ID#] и темой «#SUBJECT#» успешно добавлен',
        'EVENT_MESSAGE_ADD_ERROR' => 'Ошибка добавления почтового шаблона с типом #EVENT_NAME# и темой «#SUBJECT#». #ERROR#',
    );
    return isset($messages[$key])
        ? str_replace(array_keys($fields), array_values($fields), $messages[$key])
        : '';
}
~~~
MARKDOWN;

		$parser=new CMarkdownParser;

		$output=$parser->safeTransform($markdown);

		$this->assertContains('<pre>',$output);
		$this->assertContains('Ошибка обновления типа почтового события',$output);
	}

}
