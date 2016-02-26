<?php
/**
 * CSysLogRoute class file.
 *
 * @author miramir <gmiramir@gmail.com>
 * @author resurtm <resurtm@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2014 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CSysLogRoute dumps log messages to syslog.
 *
 * @author miramir <gmiramir@gmail.com>
 * @author resurtm <resurtm@gmail.com>
 * @package system.logging
 * @since 1.1.16
 */
class CSysLogRoute extends CLogRoute
{
	/**
	 * @var string syslog identity name.
	 */
	public $identity;
	/**
	 * @var integer syslog facility name.
	 */
	public $facility=LOG_SYSLOG;

	/**
	 * Sends log messages to syslog.
	 * @param array $logs list of log messages.
	 */
	protected function processLogs($logs)
	{
		static $syslogLevels=array(
			CLogger::LEVEL_TRACE=>LOG_DEBUG,
			CLogger::LEVEL_WARNING=>LOG_WARNING,
			CLogger::LEVEL_ERROR=>LOG_ERR,
			CLogger::LEVEL_INFO=>LOG_INFO,
			CLogger::LEVEL_PROFILE=>LOG_DEBUG,
		);

		openlog($this->identity,LOG_ODELAY|LOG_PID,$this->facility);
		foreach($logs as $log)
			syslog($syslogLevels[$log[1]],$this->formatLogMessage(str_replace("\n",', ',$log[0]),$log[1],$log[2],$log[3]));
		closelog();
	}
}
