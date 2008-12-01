<?php
/**
 * CLogger class file
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CLogger records log messages in memory.
 *
 * CLogger implements the methods to retrieve the messages with
 * various filter conditions, including log levels and log categories.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.core.log
 * @since 1.0
 */
class CLogger extends CComponent
{
	const LEVEL_TRACE='trace';
	const LEVEL_WARNING='warning';
	const LEVEL_ERROR='error';
	const LEVEL_INFO='info';
	const LEVEL_PROFILE='profile';

	/**
	 * @var array log messages
	 */
	private $_logs=array();
	/**
	 * @var array log levels for filtering (used when filtering)
	 */
	private $_levels;
	/**
	 * @var array log categories for filtering (used when filtering)
	 */
	private $_categories;

	/**
	 * Logs a message.
	 * Messages logged by this method may be retrieved back via {@link getLogs}.
	 * @param string message to be logged
	 * @param string level of the message (e.g. 'Trace', 'Warning', 'Error'). It is case-insensitive.
	 * @param string category of the message (e.g. 'system.web'). It is case-insensitive.
	 * @see getLogs
	 */
	public function log($message,$level='info',$category='application')
	{
		$this->_logs[]=array($message,$level,$category,microtime(true));
	}

	/**
	 * Retrieves log messages.
	 *
	 * Messages may be filtered by log levels and/or categories.
	 * A level filter is specified by a list of levels separated by comma or space
	 * (e.g. 'trace, error'). A category filter is similar to level filter
	 * (e.g. 'system, system.web'). A difference is that in category filter
	 * you can use pattern like 'system.*' to indicate all categories starting
	 * with 'system'.
	 *
	 * If you do not specify level filter, it will bring back logs at all levels.
	 * The same applies to category filter.
	 *
	 * Level filter and category filter are combinational, i.e., only messages
	 * satisfying both filter conditions will be returned.
	 *
	 * @param string level filter
	 * @param string category filter
	 * @return array list of messages. Each array elements represents one message
	 * with the following structure:
	 * array(
	 *   [0] => message (string)
	 *   [1] => level (string)
	 *   [2] => category (string)
	 *   [3] => timestamp (float, obtained by microtime(true));
	 */
	public function getLogs($levels='',$categories='')
	{
		$this->_levels=preg_split('/[\s,]+/',strtolower($levels),-1,PREG_SPLIT_NO_EMPTY);
		$this->_categories=preg_split('/[\s,]+/',strtolower($categories),-1,PREG_SPLIT_NO_EMPTY);
		if(empty($levels) && empty($categories))
			return $this->_logs;
		else if(empty($levels))
			return array_values(array_filter(array_filter($this->_logs,array($this,'filterByCategory'))));
		else if(empty($categories))
			return array_values(array_filter(array_filter($this->_logs,array($this,'filterByLevel'))));
		else
		{
			$ret=array_values(array_filter(array_filter($this->_logs,array($this,'filterByLevel'))));
			return array_values(array_filter(array_filter($ret,array($this,'filterByCategory'))));
		}
	}

	/**
	 * Filter function used by {@link getLogs}
	 * @param array element to be filtered
	 * @return array valid log, false if not.
	 */
	private function filterByCategory($value)
	{
		foreach($this->_categories as $category)
		{
			$cat=strtolower($value[2]);
			if($cat===$category || (($c=rtrim($category,'.*'))!==$category && strpos($cat,$c)===0))
				return $value;
		}
		return false;
	}

	/**
	 * Filter function used by {@link getLogs}
	 * @param array element to be filtered
	 * @return array valid log, false if not.
	 */
	private function filterByLevel($value)
	{
		return in_array(strtolower($value[1]),$this->_levels)?$value:false;
	}

	/**
	 * Returns the total time for serving the current request.
	 * This method calculates the difference between now and the timestamp
	 * defined by constant YII_BEGIN_TIME.
	 * To estimate the execution time more accurately, the constant should
	 * be defined as early as possible (best at the beginning of the entry script.)
	 * @return float the total time for serving the current request.
	 */
	public function getExecutionTime()
	{
		return microtime(true)-YII_BEGIN_TIME;
	}

	/**
	 * Returns the memory usage of the current application.
	 * This method relies on the PHP function memory_get_usage().
	 * If it is not available, the method will attempt to use OS programs
	 * to determine the memory usage. A value 0 will be returned if the
	 * memory usage can still not be determined.
	 * @return integer memory usage of the application (in bytes).
	 */
	public function getMemoryUsage()
	{
		if(function_exists('memory_get_usage'))
			return memory_get_usage();
		else
		{
			$output=array();
			if(strncmp(PHP_OS,'WIN',3)===0)
			{
				exec('tasklist /FI "PID eq ' . getmypid() . '" /FO LIST',$output);
				return isset($output[5])?preg_replace('/[\D]/','',$output[5])*1024 : 0;
			}
			else
			{
				$pid=getmypid();
				exec("ps -eo%mem,rss,pid | grep $pid", $output);
				$output=explode("  ",$output[0]);
				return isset($output[1]) ? $output[1]*1024 : 0;
			}
		}
	}
}
