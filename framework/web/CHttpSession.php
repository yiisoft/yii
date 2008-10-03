<?php
/**
 * CHttpSession class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CHttpSession provides session-level data management and the related configurations.
 *
 * To start the session, call {@link open()}; To complete and send out session data, call {@link close()};
 * To destroy the session, call {@link destroy()}.
 *
 * If {@link autoStart} is set true, the session will be started automatically
 * when the application component is initialized by the application.
 *
 * CHttpSession can be used like an array to set and get session data. For example,
 * <pre>
 *   $session=new CHttpSession;
 *   $session->open();
 *   $value1=$session['name1'];  // get session variable 'name1'
 *   $value2=$session['name2'];  // get session variable 'name2'
 *   foreach($session as $name=>$value) // traverse all session variables
 *   $session['name3']=$value3;  // set session variable 'name3'
 * </pre>
 *
 * The following configurations are available for session:
 * <ul>
 * <li>{@link setSessionID sessionID};</li>
 * <li>{@link setSessionName sessionName};</li>
 * <li>{@link autoStart};</li>
 * <li>{@link setSavePath savePath};</li>
 * <li>{@link setCookieParams cookieParams};</li>
 * <li>{@link useCustomStorage};</li>
 * <li>{@link setGCProbability gcProbability};</li>
 * <li>{@link setCookieMode cookieMode};</li>
 * <li>{@link setUseTransparentSessionID useTransparentSessionID};</li>
 * <li>{@link setTimeout timeout}.</li>
 * </ul>
 * See the corresponding setter and getter documentation for more information.
 * Note, these properties must be set before the session is started.
 *
 * CHttpSession can be extended to support customized session storage.
 * Override {@link openSession}, {@link closeSession}, {@link readSession},
 * {@link writeSession}, {@link destroySession} and {@link gcSession}
 * and set {@link useCustomStorage} to true.
 * Then, the session data will be stored and retrieved using the above methods.
 *
 * CHttpSession is a Web application component that can be accessed via
 * {@link CWebApplication::getSession()}.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.web
 * @since 1.0
 */
class CHttpSession extends CApplicationComponent implements IteratorAggregate,ArrayAccess,Countable
{
	/**
	 * @var boolean whether the session should be automatically started when the session application component is initialized, defaults to true.
	 */
	public $autoStart=true;
	/**
	 * @var boolean whether to use user-specified handlers to store session data.
	 * If true, make sure the methods {@link openSession}, {@link closeSession}, {@link readSession},
	 * {@link writeSession}, {@link destroySession}, and {@link gcSession} are overridden in child
	 * class, because they will be used as the callback handlers.
	 */
	public $useCustomStorage=false;


	/**
	 * Initializes the application component.
	 * This method is required by IApplicationComponent and is invoked by application.
	 */
	public function init()
	{
		parent::init();
		if($this->autoStart)
			$this->open();
		register_shutdown_function(array($this,'close'));
	}

	/**
	 * Starts the session if it has not started yet.
	 */
	public function open()
	{
		if(session_id()==='')
		{
			if($this->useCustomStorage)
				session_set_save_handler(array($this,'openSession'),array($this,'closeSession'),array($this,'readSession'),array($this,'writeSession'),array($this,'destroySession'),array($this,'gcSession'));
			session_start();
		}
	}

	/**
	 * Ends the current session and store session data.
	 */
	public function close()
	{
		if(session_id()!=='')
			@session_write_close();
	}

	/**
	 * Frees all session variables and destroys all data registered to a session.
	 */
	public function destroy()
	{
		if(session_id()!=='')
		{
			@session_unset();
			@session_destroy();
		}
	}

	/**
	 * @return boolean whether the session has started
	 */
	public function getIsStarted()
	{
		return session_id()!=='';
	}

	/**
	 * @return string the current session ID
	 */
	public function getSessionID()
	{
		return session_id();
	}

	/**
	 * @param string the session ID for the current session
	 */
	public function setSessionID($value)
	{
		session_id($value);
	}

	/**
	 * @return string the current session name
	 */
	public function getSessionName()
	{
		return session_name();
	}

	/**
	 * @param string the session name for the current session, must be an alphanumeric string, defaults to PHPSESSID
	 */
	public function setSessionName($value)
	{
		session_name($value);
	}

	/**
	 * @return string the current session save path, defaults to '/tmp'.
	 */
	public function getSavePath()
	{
		return session_save_path();
	}

	/**
	 * @param string the current session save path
	 * @throws CException if the path is not a valid directory
	 */
	public function setSavePath($value)
	{
		if(is_dir($value))
			session_save_path($value);
		else
			throw new CException(Yii::t('yii#CHttpSession.savePath "{path}" is not a valid directory.',
				array('{path}'=>$value)));
	}

	/**
	 * @return array the session cookie parameters.
	 * @see http://us2.php.net/manual/en/function.session-get-cookie-params.php
	 */
	public function getCookieParams()
	{
		return session_get_cookie_params();
	}

	/**
	 * Sets the session cookie parameters.
	 * The effect of this method only lasts for the duration of the script.
	 * Call this method before the session starts.
	 * @param array cookie parameters, valid keys include: lifetime, path, domain, secure.
	 * @see http://us2.php.net/manual/en/function.session-set-cookie-params.php
	 */
	public function setCookieParams($value)
	{
		$data=session_get_cookie_params();
		extract($data);
		extract($value);
		session_set_cookie_params($lifetime,$path,$domain,$secure);
	}

	/**
	 * @return string how to use cookie to store session ID. Defaults to 'Allow'.
	 */
	public function getCookieMode()
	{
		if(ini_get('session.use_cookies')==='0')
			return 'none';
		else if(ini_get('session.use_only_cookies')==='0')
			return 'allow';
		else
			return 'only';
	}

	/**
	 * @param string how to use cookie to store session ID. Valid values include 'none', 'allow' and 'only'.
	 */
	public function setCookieMode($value)
	{
		if($value==='none')
			ini_set('session.use_cookies','0');
		else if($value==='allow')
		{
			ini_set('session.use_cookies','1');
			ini_set('session.use_only_cookies','0');
		}
		else if($value==='only')
		{
			ini_set('session.use_cookies','1');
			ini_set('session.use_only_cookies','1');
		}
		else
			throw new CException(Yii::t('yii#CHttpSession.cookieMode can only be "none", "allow" or "only".'));
	}

	/**
	 * @return integer the probability (percentage) that the gc (garbage collection) process is started on every session initialization, defaults to 1 meaning 1% chance.
	 */
	public function getGCProbability()
	{
		return (int)ini_get('session.gc_probability');
	}

	/**
	 * @param integer the probability (percentage) that the gc (garbage collection) process is started on every session initialization.
	 * @throws CException if the value is beyond [0,100]
	 */
	public function setGCProbability($value)
	{
		$value=(int)$value;
		if($value>=0 && $value<=100)
		{
			ini_set('session.gc_probability',$value);
			ini_set('session.gc_divisor','100');
		}
		else
			throw new CException(Yii::t('yii#CHttpSession.gcProbability "{value}" is invalid. It must be an integer between 0 and 100.',
				array('{value}'=>$value)));
	}

	/**
	 * @return boolean whether transparent sid support is enabled or not, defaults to false.
	 */
	public function getUseTransparentSessionID()
	{
		return ini_get('session.use_trans_sid')==1;
	}

	/**
	 * @param boolean whether transparent sid support is enabled or not.
	 */
	public function setUseTransparentSessionID($value)
	{
		ini_set('session.use_trans_sid',$value?'1':'0');
	}

	/**
	 * @return integer the number of seconds after which data will be seen as 'garbage' and cleaned up, defaults to 1440 seconds.
	 */
	public function getTimeout()
	{
		return (int)ini_get('session.gc_maxlifetime');
	}

	/**
	 * @param integer the number of seconds after which data will be seen as 'garbage' and cleaned up
	 */
	public function setTimeout($value)
	{
		ini_set('session.gc_maxlifetime',$value);
	}

	/**
	 * Session open handler.
	 * This method should be overridden if {@link useCustomStorage} is set true.
	 * Do not call this method directly.
	 * @param string session save path
	 * @param string session name
	 * @return boolean whether session is opened successfully
	 */
	public function openSession($savePath,$sessionName)
	{
		return true;
	}

	/**
	 * Session close handler.
	 * This method should be overridden if {@link useCustomStorage} is set true.
	 * Do not call this method directly.
	 * @return boolean whether session is closed successfully
	 */
	public function closeSession()
	{
		return true;
	}

	/**
	 * Session read handler.
	 * This method should be overridden if {@link useCustomStorage} is set true.
	 * Do not call this method directly.
	 * @param string session ID
	 * @return string the session data
	 */
	public function readSession($id)
	{
		return '';
	}

	/**
	 * Session write handler.
	 * This method should be overridden if {@link useCustomStorage} is set true.
	 * Do not call this method directly.
	 * @param string session ID
	 * @param string session data
	 * @return boolean whether session write is successful
	 */
	public function writeSession($id,$data)
	{
		return true;
	}

	/**
	 * Session destroy handler.
	 * This method should be overridden if {@link useCustomStorage} is set true.
	 * Do not call this method directly.
	 * @param string session ID
	 * @return boolean whether session is destroyed successfully
	 */
	public function destroySession($id)
	{
		return true;
	}

	/**
	 * Session GC (garbage collection) handler.
	 * This method should be overridden if {@link useCustomStorage} is set true.
	 * Do not call this method directly.
	 * @param integer the number of seconds after which data will be seen as 'garbage' and cleaned up.
	 * @return boolean whether session is GCed successfully
	 */
	public function gcSession($maxLifetime)
	{
		return true;
	}

	//------ The following methods enable CHttpSession to be CMap-like -----

	/**
	 * Returns an iterator for traversing the session variables.
	 * This method is required by the interface IteratorAggregate.
	 * @return CHttpSessionIterator an iterator for traversing the session variables.
	 */
	public function getIterator()
	{
		return new CHttpSessionIterator;
	}

	/**
	 * @return integer the number of session variables
	 */
	public function getCount()
	{
		return count($_SESSION);
	}

	/**
	 * Returns the number of items in the session.
	 * This method is required by Countable interface.
	 * @return integer number of items in the session.
	 */
	public function count()
	{
		return $this->getCount();
	}

	/**
	 * @return array the list of session variable names
	 */
	public function getKeys()
	{
		return array_keys($_SESSION);
	}

	/**
	 * Returns the session variable value with the session variable name.
	 * This method is exactly the same as {@link offsetGet}.
	 * @param mixed the session variable name
	 * @return mixed the session variable value, null if no such variable exists
	 */
	public function itemAt($key)
	{
		return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
	}

	/**
	 * Adds a session variable.
	 * Note, if the specified name already exists, the old value will be removed first.
	 * @param mixed session variable name
	 * @param mixed session variable value
	 */
	public function add($key,$value)
	{
		$_SESSION[$key]=$value;
	}

	/**
	 * Removes a session variable.
	 * @param mixed the name of the session variable to be removed
	 * @return mixed the removed value, null if no such session variable.
	 */
	public function remove($key)
	{
		if(isset($_SESSION[$key]))
		{
			$value=$_SESSION[$key];
			unset($_SESSION[$key]);
			return $value;
		}
		else
			return null;
	}

	/**
	 * Removes all session variables
	 */
	public function clear()
	{
		foreach(array_keys($_SESSION) as $key)
			unset($_SESSION[$key]);
	}

	/**
	 * @param mixed session variable name
	 * @return boolean whether there is the named session variable
	 */
	public function contains($key)
	{
		return isset($_SESSION[$key]);
	}

	/**
	 * @return array the list of all session variables in array
	 */
	public function toArray()
	{
		return $_SESSION;
	}

	/**
	 * This method is required by the interface ArrayAccess.
	 * @param mixed the offset to check on
	 * @return boolean
	 */
	public function offsetExists($offset)
	{
		return isset($_SESSION[$offset]);
	}

	/**
	 * This method is required by the interface ArrayAccess.
	 * @param integer the offset to retrieve element.
	 * @return mixed the element at the offset, null if no element is found at the offset
	 */
	public function offsetGet($offset)
	{
		return isset($_SESSION[$offset]) ? $_SESSION[$offset] : null;
	}

	/**
	 * This method is required by the interface ArrayAccess.
	 * @param integer the offset to set element
	 * @param mixed the element value
	 */
	public function offsetSet($offset,$item)
	{
		$_SESSION[$offset]=$item;
	}

	/**
	 * This method is required by the interface ArrayAccess.
	 * @param mixed the offset to unset element
	 */
	public function offsetUnset($offset)
	{
		unset($_SESSION[$offset]);
	}
}

/**
 * CHttpSessionIterator implements an interator for {@link CHttpSession}.
 *
 * It allows CHttpSession to return a new iterator for traversing the session variables.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.web
 * @since 1.0
 */
class CHttpSessionIterator implements Iterator
{
	/**
	 * @var array list of keys in the map
	 */
	private $_keys;
	/**
	 * @var mixed current key
	 */
	private $_key;

	/**
	 * Constructor.
	 * @param array the data to be iterated through
	 */
	public function __construct()
	{
		$this->_keys=array_keys($_SESSION);
	}

	/**
	 * Rewinds internal array pointer.
	 * This method is required by the interface Iterator.
	 */
	public function rewind()
	{
		$this->_key=reset($this->_keys);
	}

	/**
	 * Returns the key of the current array element.
	 * This method is required by the interface Iterator.
	 * @return mixed the key of the current array element
	 */
	public function key()
	{
		return $this->_key;
	}

	/**
	 * Returns the current array element.
	 * This method is required by the interface Iterator.
	 * @return mixed the current array element
	 */
	public function current()
	{
		return isset($_SESSION[$this->_key])?$_SESSION[$this->_key]:null;
	}

	/**
	 * Moves the internal pointer to the next array element.
	 * This method is required by the interface Iterator.
	 */
	public function next()
	{
		do
		{
			$this->_key=next($this->_keys);
		}
		while(!isset($_SESSION[$this->_key]) && $this->_key!==false);
	}

	/**
	 * Returns whether there is an element at current position.
	 * This method is required by the interface Iterator.
	 * @return boolean
	 */
	public function valid()
	{
		return $this->_key!==false;
	}
}
