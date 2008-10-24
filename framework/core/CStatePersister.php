<?php
/**
 * This file contains classes implementing security manager feature.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CStatePersister implements a file-based persistent data storage.
 *
 * It can be used to keep data available through multiple requests and sessions.
 *
 * By default, CStatePersister stores data in a file named 'state.bin' that is located
 * under the application {@link CApplication::getRuntimePath runtime path}.
 * You may change the location by setting the {@link setStateFile stateFile} property.
 *
 * To retrieve the data from CStatePersister, call {@link load()}. To save the data,
 * call {@link save()}.
 *
 * Comparison among state persister, session and cache is as follows:
 * <ul>
 * <li>session: data persisting within a single user session.</li>
 * <li>state persister: data persisting through all requests/sessions (e.g. hit counter).</li>
 * <li>cache: volatile and fast storage. It may be used as storage medium for session or state persister.</li>
 * </ul>
 *
 * Since server resource is often limited, be cautious if you plan to use CStatePersister
 * to store large amount of data. You should also consider using database-based persister
 * to improve the throughput.
 *
 * CStatePersister is a core application component used to store global application state.
 * It may be accessed via {@link CApplication::getStatePersister)}.
 * page state persistent method based on cache.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.core
 * @since 1.0
 */
class CStatePersister extends CApplicationComponent implements IStatePersister
{
	private $_stateFile;

	/**
	 * @return string the absolute file path storing the state data
	 */
	public function getStateFile()
	{
		if($this->_stateFile!==null)
			return $this->_stateFile;
		else
			return $this->_stateFile=Yii::app()->getRuntimePath().DIRECTORY_SEPARATOR.'state.bin';
	}

	/**
	 * @param string the file path storing the state data. Make sure the directory containing
	 * the file exists and is writable by the Web server process. If using relative path, also
	 * make sure the path is correct.
	 */
	public function setStateFile($value)
	{
		if(($path=realpath(dirname($value)))===false || !is_dir($path) || !is_writable($path))
			throw new CException(Yii::t('yii','Unable to create application state file "{file}". Make sure the directory containing the file exists and is writable by the Web server process.',
				array('{file}'=>$value)));
		$this->_stateFile=$path.DIRECTORY_SEPARATOR.basename($value);
	}

	/**
	 * Loads state data from persistent storage.
	 * @return mixed state data. Null if no state data available.
	 */
	public function load()
	{
		$stateFile=$this->getStateFile();
		if(($cache=Yii::app()->getCache())!==null)
		{
			$cacheKey='file:'.$stateFile;
			if(($value=$cache->get($cacheKey))!==false)
				return unserialize($value);
			else if(($content=@file_get_contents($stateFile))!==false)
			{
				$cache->set($cacheKey,$content,0,new CFileCacheDependency($stateFile));
				return unserialize($content);
			}
			else
				return null;
		}
		else if(($content=@file_get_contents($stateFile))!==false)
			return unserialize($content);
		else
			return null;
	}

	/**
	 * Saves application state in persistent storage.
	 * @param mixed state data (must be serializable).
	 */
	public function save($state)
	{
		file_put_contents($this->getStateFile(),serialize($state),LOCK_EX);
	}
}
