<?php
/**
 * CPhpMessageSource class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CPhpMessageSource represents a message source that stores translated messages in PHP scripts.
 *
 * CPhpMessageSource uses PHP files and arrays to keep message translations.
 * <ul>
 * <li>All translations are saved under the {@link basePath} directory.</li>
 * <li>Translations in one language are kept as PHP files under an individual subdirectory
 *   whose name is the same as the language ID. Each PHP file contains messages
 *   belonging to the same category, and the file name is the same as the category name.</li>
 * <li>Within a PHP file, an array of (source, translation) pairs is returned.
 * For example:
 * <pre>
 * return array(
 *     'original message 1' => 'translated message 1',
 *     'original message 2' => 'translated message 2',
 * );
 * </pre>
 * </li>
 * </ul>
 * When {@link cachingDuration} is set as a positive number, message translations will be cached.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.i18n
 * @since 1.0
 */
class CPhpMessageSource extends CMessageSource
{
	const CACHE_KEY_PREFIX='Yii.CPhpMessageSource.';

	/**
	 * @var integer the time in seconds that the messages can remain valid in cache.
	 * Defaults to 0, meaning the caching is disabled.
	 */
	public $cachingDuration=0;
	/**
	 * @var string the base path for all translated messages. Defaults to null, meaning
	 * the "messages" subdirectory of the application directory (e.g. "protected/messages").
	 */
	public $basePath;

	/**
	 * Initializes the application component.
	 * This method overrides the parent implementation by preprocessing
	 * the user request data.
	 */
	public function init()
	{
		parent::init();
		if($this->basePath===null)
			$this->basePath=Yii::getPathOfAlias('application.messages');
	}

	/**
	 * Loads the message translation for the specified language and category.
	 * @param string the message category
	 * @param string the target language
	 * @return array the loaded messages
	 */
	protected function loadMessages($category,$language)
	{
		$messageFile=$this->basePath.DIRECTORY_SEPARATOR.$language.DIRECTORY_SEPARATOR.$category.'.php';

		if($this->cachingDuration>0 && ($cache=Yii::app()->getCache())!==null)
		{
			$key=self::CACHE_KEY_PREFIX . $messageFile;
			if(($data=$cache->get($key))!==false)
				return unserialize($data);
		}

		if(is_file($messageFile))
		{
			$messages=include($messageFile);
			if(!is_array($messages))
				$messages=array();
			if(isset($cache))
			{
				$dependency=new CFileCacheDependency($messageFile);
				$cache->set($key,serialize($messages),$this->cachingDuration,$dependency);
			}
			return $messages;
		}
		else
			return array();
	}
}