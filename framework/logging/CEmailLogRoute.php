<?php
/**
 * CEmailLogRoute class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CEmailLogRoute sends selected log messages to email addresses.
 *
 * The target email addresses may be specified via {@link setEmails emails} property.
 * Optionally, you may set the email {@link setSubject subject} and the
 * {@link setSentFrom sentFrom} address.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.logging
 * @since 1.0
 */
class CEmailLogRoute extends CLogRoute
{
	/**
	 * Default email subject.
	 */
	const DEFAULT_SUBJECT='Application Log';
	/**
	 * @var array list of destination email addresses.
	 */
	private $_email=array();
	/**
	 * @var string email subject
	 */
	private $_subject='';
	/**
	 * @var string email sent from address
	 */
	private $_from='';

	/**
	 * Sends log messages to specified email addresses.
	 * @param array list of log messages
	 */
	protected function processLogs($logs)
	{
		$message='';
		foreach($logs as $log)
			$message.=$this->formatLogMessage($log[0],$log[1],$log[2],$log[3]);
		$message=wordwrap($message,70);
		foreach($this->getEmails() as $email)
			$this->sendEmail($email,$this->getSubject(),$message);
	}

	/**
	 * Sends an email.
	 * @param string single email address
	 * @param string email subject
	 * @param string email content
	 */
	protected function sendEmail($email,$subject,$message)
	{
		if(($from=$this->getSentFrom())!=='')
			mail($email,$subject,$message,"From:{$from}\r\n");
		else
			mail($email,$subject,$message);
	}

	/**
	 * @return array list of destination email addresses
	 */
	public function getEmails()
	{
		return $this->_email;
	}

	/**
	 * @return array|string list of destination email addresses. If the value is
	 * a string, it is assumed to be comma-separated email addresses.
	 */
	public function setEmails($value)
	{
		if(is_array($value))
			$this->_email=$value;
		else
			$this->_email=preg_split('/[\s,]+/',$value,-1,PREG_SPLIT_NO_EMPTY);
	}

	/**
	 * @return string email subject. Defaults to CEmailLogRoute::DEFAULT_SUBJECT
	 */
	public function getSubject()
	{
		return $this->_subject;
	}

	/**
	 * @param string email subject.
	 */
	public function setSubject($value)
	{
		$this->_subject=$value;
	}

	/**
	 * @return string send from address of the email
	 */
	public function getSentFrom()
	{
		return $this->_from;
	}

	/**
	 * @param string send from address of the email
	 */
	public function setSentFrom($value)
	{
		$this->_from=$value;
	}
}

