<?php
/**
 * CGettextPoFile class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CGettextPoFile represents a PO Gettext message file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.i18n.gettext
 * @since 1.0
 */
class CGettextPoFile extends CGettextFile
{
	/**
	 * Loads messages from a PO file.
	 * @param string file path
	 * @return array message translations (source message => translated message)
	 */
	public function load($file)
	{
		$content=file_get_contents($file);
        $n=preg_match_all('/msgid\s+"(.*?(?<!\\\\))"\s+msgstr\s+"(.*?(?<!\\\\))"/',$content,$matches);
        $messages=array();
        for($i=0;$i<$n;++$i)
        {
        	$id=$this->decode($matches[1][$i]);
        	$message=$this->decode($matches[2][$i]);
        	$messages[$id]=$message;
        }
        return $messages;
	}

	/**
	 * Saves messages to a PO file.
	 * @param string file path
	 * @param array message translations (source message => translated message)
	 */
	public function save($file,$messages)
	{
		$content='';
		foreach($messages as $id=>$message)
		{
			$content.='msgid "'.$this->encode($id)."\"\n";
			$content.='msgstr "'.$this->encode($message)."\"\n\n";
		}
		file_put_contents($file,$content);
	}

	/**
	 * Encodes special characters in a message.
	 * @param string message to be encoded
	 * @return string the encoded message
	 */
	protected function encode($string)
	{
		return str_replace(array('"', "\n", "\t", "\r"),array('\\"', "\\n", '\\t', '\\r'),$string);
	}

	/**
	 * Decodes special characters in a message.
	 * @param string message to be decoded
	 * @return string the decoded message
	 */
	protected function decode($string)
	{
		return str_replace(array('\\"', "\\n", '\\t', '\\r'),array('"', "\n", "\t", "\r"),$string);
	}
}