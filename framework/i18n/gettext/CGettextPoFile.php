<?php
/**
 * CGettextPoFile class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link https://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

/**
 * CGettextPoFile represents a PO Gettext message file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.i18n.gettext
 * @since 1.0
 */
class CGettextPoFile extends CGettextFile
{
	/**
	 * @var string quoted PO string confined to one physical line. Possessive repetition
	 * avoids exhausting the PCRE stack on long strings and continuation sections.
	 */
	private static $_quotedStringPattern='"(?:[^"\\\\\r\n]|\\\\[^\r\n])*+"';

	/**
	 * Loads messages from a PO file.
	 * @param string $file file path
	 * @param string $context message context
	 * @return array message translations (source message => translated message)
	 * @throws CException if a regular expression fails
	 */
	public function load($file,$context)
	{
		// A field may continue on the next line, but never across a blank entry boundary.
		$spacing='[ \t]*(?:\r\n|\r|\n)?[ \t]*';
		$quoted=self::$_quotedStringPattern.$spacing;
		$pattern='/(msgctxt(?=[ \t\r\n])'.$spacing.'((?:'.$quoted.')++))?'
			.'msgid'.$spacing.'((?:'.$quoted.')++)'
			.'msgstr'.$spacing.'((?:'.$quoted.')++)/';
		$matches=array();
		$n=preg_match_all($pattern,file_get_contents($file),$matches);
		if($n===false)
			throw new CException('Unable to parse PO file "'.$file.'".');

		$messages=array();
		for($i=0; $i<$n; $i++)
		{
			if($this->decode($matches[2][$i])===$context)
			{
				$id=$this->decode($matches[3][$i]);
				$message=$this->decode($matches[4][$i]);
				$messages[$id]=$message;
			}
		}
		return $messages;
	}

	/**
	 * Saves messages to a PO file.
	 * @param string $file file path
	 * @param array $messages message translations (message id => translated message).
	 * Note if the message has a context, the message id must be prefixed with
	 * the context with chr(4) as the separator.
	 */
	public function save($file,$messages)
	{
		$content='';
		foreach($messages as $id=>$message)
		{
			if(($pos=strpos($id,chr(4)))!==false)
			{
				$content.='msgctxt "'.$this->encode(substr($id,0,$pos))."\"\n";
				$id=substr($id,$pos+1);
			}
			$content.='msgid "'.$this->encode($id)."\"\n";
			$content.='msgstr "'.$this->encode($message)."\"\n\n";
		}
		file_put_contents($file,$content);
	}

	/**
	 * Encodes special characters in a message.
	 * @param string $string message to be encoded
	 * @return string the encoded message
	 */
	protected function encode($string)
	{
		return str_replace(
			array('\\','"',"\n","\t","\r"),
			array('\\\\','\\"',"\\n",'\\t','\\r'),
			$string
		);
	}

	/**
	 * Decodes special characters in a message.
	 * @param string $string message to be decoded
	 * @return string the decoded message
	 * @throws CException if a regular expression fails
	 */
	protected function decode($string)
	{
		if(preg_match_all('/'.self::$_quotedStringPattern.'/',$string,$matches)===false)
			throw new CException('Unable to decode PO string.');

		$decoded='';
		foreach($matches[0] as $fragment)
		{
			$decoded.=strtr(substr($fragment,1,-1),array(
				'\\n'=>"\n",
				'\\r'=>"\r",
				'\\t'=>"\t",
				'\\"'=>'"',
				'\\\\'=>'\\',
			));
		}
		return $decoded;
	}
}
