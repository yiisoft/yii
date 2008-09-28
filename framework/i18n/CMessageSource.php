<?php
/**
 * CMessageSource class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CMessageSource is the base class for message translation repository classes.
 *
 * A message source is an application component that provides message internationalization (i18n).
 * It stores messages translated in different languages and provides
 * these translated versions when requested.
 *
 * A concrete class must implement {@link loadMessages} or override {@link translateMessage}.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.i18n
 * @since 1.0
 */
abstract class CMessageSource extends CApplicationComponent
{
	private $_language;
	private $_messages=array();

	/**
	 * Loads the message translation for the specified language and category.
	 * @param string the message category
	 * @param string the target language
	 * @return array the loaded messages
	 */
	abstract protected function loadMessages($category,$language);

	/**
	 * @return string the language that the source messages are written in.
	 * Defaults to {@link CApplication::language application language}.
	 */
	public function getLanguage()
	{
		return $this->_language===null ? Yii::app()->sourceLanguage : $this->_language;
	}

	/**
	 * @param string the language that the source messages are written in.
	 */
	public function setLanguage($language)
	{
		$this->_language=CLocale::getCanonicalID($language);
	}

	/**
	 * Translates a message to the {@link CApplication::getLanguage application language}.
	 * Note, if the {@link CApplication::getLanguage application language} is the same as
	 * the {@link getLanguage source message language}, messages will NOT be translated.
	 * @param string the message to be translated
	 * @param string the message category
	 * @return string the translated message (or the original message if translation is not needed)
	 */
	public function translate($message,$category)
	{
		if(($lang=Yii::app()->getLanguage())!==$this->getLanguage())
			return $this->translateMessage($message,$category,$lang);
		else
			return $message;
	}

	/**
	 * Translates the specified message.
	 * @param string the message to be translated
	 * @param string the category that the message belongs to
	 * @param string the target language
	 * @return string the translated message
	 */
	protected function translateMessage($message,$category,$language)
	{
		$key=$language.'.'.$category;
		if(!isset($this->_messages[$key]))
			$this->_messages[$key]=$this->loadMessages($category,$language);
		if(isset($this->_messages[$key][$message]) && $this->_messages[$key][$message]!=='')
			return $this->_messages[$key][$message];
		else
			return $message;
	}
}
