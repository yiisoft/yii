<?php
/**
 * CPluralFormatter class file.
 *
 * @author Nikola Kovacs <nikola.kovacs[at]gmail[dot]com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CPluralFormatter chooses an appropriate message based on locale-dependent
 * plural rules using CChoiceFormat.
 */
class CPluralFormatter extends CComponent
{
	private $_locale;

	/**
	 * Constructor.
	 * @param mixed $locale locale ID (string) or CLocale instance
	 */
	public function __construct($locale)
	{
		if(is_string($locale))
			$this->_locale=CLocale::getInstance($locale);
		else
			$this->_locale=$locale;
	}

	/**
	 * Formats a message according to the specified number value.
	 * @param string $messages the candidate messages in the format of 'message1|message2|message3'.
	 * See {@link CChoiceFormat} for more details.
	 * @param mixed $number the number value
	 * @return string the selected message
	 */
	public function format($messages, $number)
	{
		if(strpos($messages,'#')===false)
		{
			$chunks=explode('|',$messages);
			$expressions=$this->_locale->getPluralRules();
			if($n=min(count($chunks),count($expressions)))
			{
				for($i=0;$i<$n;$i++)
					$chunks[$i]=$expressions[$i].'#'.$chunks[$i];

				$messages=implode('|',$chunks);
			}
		}
		return CChoiceFormat::format($messages,$number);
	}

}
