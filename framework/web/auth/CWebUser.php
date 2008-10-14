<?php
/**
 * CWebUser class file
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CWebUser represents the persistent state for a Web application user.
 *
 * CWebUser is used as an application component whose ID is 'user'.
 * Therefore, at any place one can access the user state via
 * <code>Yii::app()->user</code>.
 *
 * CWebUser should be used together with an {@link IUserIdentity identity} and an optional
 * {@link roleProvider role provider}. The former authenticates a user,
 * while the latter provides the role information for the authenticated user.
 *
 * A typical authentication process using CWebUser is as follows:
 * <ol>
 * <li>The user provides information needed for authentication.</li>
 * <li>An {@link IUserIdentity identity instance} is created with the user-provided information.</li>
 * <li>Call {@link IUserIdentity::authenticate} to check if the identity is valid.</li>
 * <li>If valid, call {@link CWebUser::login} to login the user, and
 *     Redirect the user browser to {@link returnUrl}.</li>
 * <li>If not valid, retrieve the error code or message from the identity
 * instance and display it.</li>
 * </ol>
 *
 * The property {@link name} is a unique identifier for a user that is persistent
 * during the whole user session. It can be a username, or something else,
 * depending on the implementation of the {@link IUserIdentity identity class}.
 *
 * Besides {@link name}, an identity may have additional data that are also persistent
 * during the session. To access these data, call {@link getState}.
 * Note, when {@link allowAutoLogin cookie-based authentication} is enabled,
 * all these persistent data will be stored in cookie. Therefore, do not
 * store password or other sensitive data in the persistent storage. Instead,
 * you should store them in session on the server side if needed.
 *
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.web.auth
 * @since 1.0
 */
class CWebUser extends CApplicationComponent implements IWebUser
{
	const STATE_ROLES='roles';
	const FLASH_KEY_PREFIX='Yii.CWebUser.flash.';
	const FLASH_COUNTERS='Yii.CWebUser.flash.counters';

	/**
	 * @var boolean whether to enable cookie-based login. Defaults to false.
	 */
	public $allowAutoLogin=false;
	/**
	 * @var string the name for a guest user. Defaults to 'Guest'.
	 * This is used by {@link getName} when the current user is a guest (not authenticated).
	 */
	public $guestName='Guest';
	/**
	 * @var string|array the URL for login. If using array, the first element should be
	 * the route to the login action, and the rest name-value pairs are GET parameters
	 * to construct the login URL (e.g. array('site/login')).
	 * @see CController::createUrl
	 */
	public $loginUrl=array('site/login');
	/**
	 * @var string the ID of the role provider application component.
	 * Defaults to 'roleProvider'. The role provider has to implement
	 * the interface {@link IRoleProvider}.
	 */
	public $roleProvider='roleProvider';

	private $_keyPrefix;

	/**
	 * Initializes the application component.
	 * This method overrides the parent implementation by starting session,
	 * performing cookie-based authentication if enabled, and updating the flash variables.
	 */
	public function init()
	{
		parent::init();
		Yii::app()->getSession()->open();
		if($this->getIsGuest() && $this->allowAutoLogin)
			$this->restoreFromCookie();
		$this->updateFlash();
	}

	/**
	 * Logs in a user.
	 *
	 * The user identity information will be saved in storage that is
	 * persistent during the user session. By default, the storage is simply
	 * the session storage. If the duration parameter is greater than 0,
	 * a cookie will be sent to prepare for cookie-based login in future.
	 *
	 * Note, you have to set {@link allowAutoLogin} to true
	 * if you want to allow user to be authenticated based on the cookie information.
	 *
	 * @param IUserIdentity the user identity (which should already be authenticated)
	 * @param integer number of seconds that the user can remain in logged-in status. Defaults to 0, meaning login till the user closes the browser.
	 * If greater than 0, cookie-based login will be used. In this case, {@link allowAutoLogin}
	 * must be set true, otherwise an exception will be thrown.
	 */
	public function login($identity,$duration=0)
	{
		$this->setName($identity->getName());
		$this->loadIdentityStates($identity->getPersistentStates());

		if($duration>0)
		{
			if($this->allowAutoLogin)
				$this->saveToCookie($duration);
			else
				throw new CException(Yii::t('yii#{class}.allowAutoLogin must be set true in order to use cookie-based authentication.',
					array('{class}'=>get_class($this))));
		}
	}

	/**
	 * Logs out the current user.
	 * The session will be destroyed.
	 */
	public function logout()
	{
		if($this->allowAutoLogin)
			Yii::app()->getRequest()->getCookies()->remove($this->getStateKeyPrefix());
		$this->clearStates();
	}

	/**
	 * @return boolean whether the current application user is a guest.
	 */
	public function getIsGuest()
	{
		return $this->getState('_name')===null;
	}

	/**
	 * Returns the unique identifier for the user (e.g. username).
	 * This is the unique identifier that is mainly used for display purpose.
	 * @return string the user name. If the user is not logged in, this will be {@link guestName}.
	 */
	public function getName()
	{
		if(($name=$this->getState('_name'))!==null)
			return $name;
		else
			return $this->guestName;
	}

	/**
	 * Sets the unique identifier for the user (e.g. username).
	 * @param string the user name.
	 * @see getName
	 */
	public function setName($value)
	{
		$this->setState('_name',$value);
	}

	/**
	 * Returns the URL that the user should be redirected to after successful login.
	 * This property is usually used by the login action. If the login is successful,
	 * the action should read this property and use it to redirect the user browser.
	 * @return string the URL that the user should be redirected to after login. Defaults to the application entry URL.
	 * @see loginRequired
	 */
	public function getReturnUrl()
	{
		return $this->getState('_returnUrl',Yii::app()->getRequest()->getScriptUrl());
	}

	/**
	 * @param string the URL that the user should be redirected to after login.
	 */
	public function setReturnUrl($value)
	{
		$this->setState('_returnUrl',$value);
	}

	/**
	 * Checks if the user belongs to the specified role.
	 * If an application component named {@link roleProvider} exists,
	 * it will perform the actual checking work.
	 * Otherwise, a {@link getState persistent state} named 'roles' will
	 * be used to check if the specified role is in the persistent roles array.
	 * In the latter case, the comparison is case-insensitive.
	 * @param string role name
	 * @return whether the user belongs to the specified role
	 * @see roleProvider
	 */
	public function isInRole($role)
	{
		if(($roleProvider=Yii::app()->getComponent($this->roleProvider))!==null)
			return $roleProvider->isInRole($this,$role);
		else
		{
			$roles=array_map('strtolower',$this->getState(self::STATE_ROLES,array()));
			return in_array(strtolower($role),$roles);
		}
	}

	/**
	 * Redirects the user browser to the login page.
	 * Before the redirection, the current URL will be kept in {@link returnUrl}
	 * so that the user browser may be redirected back to the current page after successful login.
	 * Make sure you set {@link loginUrl} so that the user browser
	 * can be redirected to the specified login URL after calling this method.
	 * After calling this method, the current request processing will be terminated.
	 */
	public function loginRequired()
	{
		$app=Yii::app();
		$request=$app->getRequest();
		$this->setReturnUrl($request->getUrl());
		if(($url=$this->loginUrl)!==null)
		{
			if(is_array($url))
			{
				$route=isset($url[0]) ? $url[0] : $app->defaultController;
				$url=$app->createUrl($route,array_splice($url,1));
			}
			$request->redirect($url);
		}
		else
			throw new CHttpException(401,Yii::t('yii#Login Required'));
	}

	/**
	 * Populates the current user object with the information obtained from cookie.
	 * This method is used when automatic login ({@link allowAutoLogin}) is enabled.
	 * The user identity information is recovered from cookie.
	 * Sufficient security measures are used to prevent cookie data from being tampered.
	 * @see saveToCookie
	 */
	protected function restoreFromCookie()
	{
		$app=Yii::app();
		$cookie=$app->getRequest()->getCookies()->itemAt($this->getStateKeyPrefix());
		if($cookie && !empty($cookie->value) && ($data=$app->getSecurityManager()->validateData($cookie->value))!==false)
		{
			$data=unserialize($data);
			if(isset($data[0],$data[1],$data[2]))
			{
				list($name,$address,$states)=$data;
				if($address===$app->getRequest()->getUserHostAddress())
				{
					$this->setName($name);
					$this->loadIdentityStates($states);
					$this->onRestoreFromCookie(new CEvent($this));
				}
			}
		}
	}

	/**
	 * Raised when the user identity information is being restored from cookie.
	 * This event is only raised when {@link allowAutoLogin} is true and when
	 * the user identity information is being restored from cookie.
	 * When this event is raised, the user component already has the unique ID available.
	 * @param CEvent event parameter
	 */
	public function onRestoreFromCookie($event)
	{
		$this->raiseEvent('onRestoreFromCookie',$event);
	}

	/**
	 * Saves necessary user data into a cookie.
	 * This method is used when automatic login ({@link allowAutoLogin}) is enabled.
	 * This method saves username, user IP address and a validation key to cookie.
	 * These information are used to do authentication next time when user visits the application.
	 * @param integer number of seconds that the user can remain in logged-in status. Defaults to 0, meaning login till the user closes the browser.
	 * @see restoreFromCookie
	 */
	protected function saveToCookie($duration)
	{
		$app=Yii::app();
		$cookie=new CHttpCookie($this->getStateKeyPrefix(),'');
		$cookie->expire=time()+$duration;
		$data=array(
			$this->getName(),
			$app->getRequest()->getUserHostAddress(),
			$this->saveIdentityStates(),
		);
		$cookie->value=$app->getSecurityManager()->hashData(serialize($data));
		$app->getRequest()->getCookies()->add($cookie->name,$cookie);
	}

	/**
	 * @return string a prefix for the name of the session variables storing user session data.
	 */
	protected function getStateKeyPrefix()
	{
		if($this->_keyPrefix!==null)
			return $this->_keyPrefix;
		else
			return $this->_keyPrefix=md5('Yii.'.get_class($this).'.'.Yii::app()->getId());
	}

	/**
	 * Returns the value of a variable that is stored in user session.
	 *
	 * This function is designed to be used by CWebUser descendant classes
	 * who want to store additional user information in user session.
	 * A variable, if stored in user session using {@link setState} can be
	 * retrieved back using this function.
	 *
	 * @param string variable name
	 * @param mixed default value
	 * @return mixed the value of the variable. If it doesn't exist in the session,
	 * the provided default value will be returned
	 * @see setState
	 */
	public function getState($key,$defaultValue=null)
	{
		$key=$this->getStateKeyPrefix().$key;
		return isset($_SESSION[$key]) ? $_SESSION[$key] : $defaultValue;
	}

	/**
	 * Stores a variable in user session.
	 *
	 * This function is designed to be used by CWebUser descendant classes
	 * who want to store additional user information in user session.
	 * By storing a variable using this function, the variable may be retrieved
	 * back later using {@link getState}. The variable will be persistent
	 * across page requests during a user session.
	 *
	 * @param string variable name
	 * @param mixed variable value
	 * @param mixed default value. If $value===$defaultValue, the variable will be
	 * removed from the session
	 * @see getState
	 */
	public function setState($key,$value,$defaultValue=null)
	{
		$key=$this->getStateKeyPrefix().$key;
		if($value===$defaultValue)
			unset($_SESSION[$key]);
		else
			$_SESSION[$key]=$value;
	}

	/**
	 * Clears all user identity information from persistent storage.
	 * The default implementation simply destroys the session.
	 */
	public function clearStates()
	{
		Yii::app()->getSession()->destroy();
	}

	/**
	 * Returns a flash message.
	 * A flash message is available only in the current and the next requests.
	 * @param string key identifying the flash message
	 * @param mixed value to be returned if the flash message is not available.
	 * @return mixed the message message
	 */
	public function getFlash($key,$defaultValue=null)
	{
		return $this->getState(self::FLASH_KEY_PREFIX.$key,$defaultValue);
	}

	/**
	 * Stores a flash message.
	 * A flash message is available only in the current and the next requests.
	 * @param string key identifying the flash message
	 * @param mixed flash message
	 * @param mixed if this value is the same as the flash message, the flash message
	 * will be removed. (Therefore, you can use setFlash('key',null) to remove a flash message.)
	 */
	public function setFlash($key,$value,$defaultValue=null)
	{
		$this->setState(self::FLASH_KEY_PREFIX.$key,$value,$defaultValue);
		$counters=$this->getState(self::FLASH_COUNTERS,array());
		if($value===$defaultValue)
			unset($counters[$key]);
		else
			$counters[$key]=0;
		$this->setState(self::FLASH_COUNTERS,$counters,array());
	}

	/**
	 * @param string key identifying the flash message
	 * @return boolean whether the specified flash message exists
	 */
	public function hasFlash($key)
	{
		return $this->getFlash($key)!==null;
	}

	/**
	 * Retrieves identity states from persistent storage and saves them as an array.
	 * @return array the identity states
	 */
	protected function saveIdentityStates()
	{
		$states=array();
		foreach($this->getState('_states',array()) as $name)
			$states[$name]=$this->getState($name);
		return $states;
	}

	/**
	 * Loads identity states from an array and saves them to persistent storage.
	 * @param array the identity states
	 */
	protected function loadIdentityStates($states)
	{
		if(is_array($states))
		{
			foreach($states as $name=>$value)
				$this->setState($name,$value);
			$this->setState('_states',array_keys($states));
		}
		else
			$this->setState('_states',array());
	}

	/**
	 * Updates the internal counters for flash messages.
	 * This method is internally used by {@link CWebApplication}
	 * to maintain the availability of flash messages.
	 */
	protected function updateFlash()
	{
		$counters=$this->getState(self::FLASH_COUNTERS);
		if(!is_array($counters))
			return;
		foreach($counters as $key=>$count)
		{
			if($count)
			{
				unset($counters[$key]);
				$this->setState(self::FLASH_KEY_PREFIX.$key,null);
			}
			else
				$counters[$key]++;
		}
		$this->setState(self::FLASH_COUNTERS,$counters,array());
	}
}
