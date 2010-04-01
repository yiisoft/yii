<?php
/**
 * CStringHelper class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CStringHelper provides a set of helper methods for string manipulations.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.utils
 * @since 1.1.2
 */
class CStringHelper
{
	/**
	 * Converts a word to its plural form.
	 * For example, 'apple' will become 'apples', and 'child' will become 'children'.
	 * @param string the word to be pluralized
	 * @return string the pluralized word
	 */
	public static function plural($name)
	{
		$rules=array(
			'/(x|ch|ss|sh|us|as|is|os)$/i' => '\1es',
			'/(?:([^f])fe|([lr])f)$/i' => '\1\2ves',
			'/(m)an$/i' => '\1en',
			'/(child)$/i' => '\1ren',
			'/(r)y$/i' => '\1ies',
			'/s$/' => 's',
		);
		foreach($rules as $rule=>$replacement)
		{
			if(preg_match($rule,$name))
				return preg_replace($rule,$replacement,$name);
		}
		return $name.'s';
	}

	/**
	 * Converts a camel-case string into a readable lower-case ID.
	 * For example, 'PostTag' will be converted as 'post-tag'.
	 * @param string the string to be converted
	 * @return string the resulting ID
	 */
	public static function id($name)
	{
		return trim(strtolower(str_replace('_','-',preg_replace('/(?<![A-Z])[A-Z]/', '-\0', $name))),'-');
	}

	/**
	 * Converts a camel-case string into space-separated words.
	 * For example, 'PostTag' will be converted as 'Post Tag'.
	 * @param string the string to be converted
	 * @param boolean whether to capitalize the first letter in each word
	 * @return string the resulting words
	 */
	public static function words($name,$ucwords=true)
	{
		$result=trim(strtolower(str_replace('_',' ',preg_replace('/(?<![A-Z])[A-Z]/', ' \0', $name))));
		return $ucwords ? ucwords($result) : $result;
	}
}
