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
 * CWebUser is the base class for objects representing user session data.
 *
 * See {@link CWebApplication::getUser()} for more information about accessing
 * the user session data when processing a Web request.
 *
 * CWebUser implements a basic auth framework that provides login and logout
 * functionalities. It also provides access to the user-related data
 * (e.g. username, roles) that are persistent through the user session.
 *
 * Derived classes must implement the {@link validate()} method. It is also
 * recommended that you override {@link getValidationKey()} if cookie-based
 * authentication is enabled (by setting {@link allowAutoLogin} to true).
 *
 * If you want to store information other than {@link getUsername username} in
 * the session, you should override {@link switchTo()} method and populate the
 * additional properties there.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.web
 * @since 1.0
 */
class CWebUser extends CApplicationComponent
{
	const FLASH_KEY_PREFIX='Yii.CWebUser.flash.';
	const FLASH_COUNTERS='Yii.CWebUser.flash.counters';

	/**
	 * @var boolean whether to enable cookie-based login. Defaults to false.
	 */
	public $allowAutoLogin=false;
	/**
	 * @var string|array the URL for login. If using array, the first element should be
	 * the route to the login action, and the rest name-value pairs are GET parameters
	 * to construct the login URL (e.g. array('user/login')).
	 * @see CController::createUrl
	 */
	public $loginUrl;

	private $_keyPrefix;

	/**
	 * Initializes the application component.
	 * This method overrides the parent implementation by trying to perform
	 * cookie-based authentication and updating the flash variables.
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
	 * Returns the user ID.
	 * Note, this property is provided here for convenience.
	 * It is not required by the auth framework. If you want to use it,
	 * you have to override {@link switchTo} to populate this information
	 * from some persistent storage (such as database).
	 * @return integer user ID. Defaults to -1, meaning an invalid ID (or guest user ID).
	 * @see switchTo
	 */
	public function getId()
	{
		return $this->getState('ID',-1);
	}

	/**
	 * @param integer user ID.
	 */
	public function setId($value)
	{
		$this->setState('id',$value,-1);
	}

	/**
	 * @return string username. Defaults to {@link getGuestName guestName}.
	 */
	public function getUsername()
	{
		return $this->getState('username',$this->getGuestName());
	}

	/**
	 * @param string username
	 */
	public function setUsername($value)
	{
		$this->setState('username',$value,$this->getGuestName());
	}

	/**
	 * @return array list of roles that this user is in.
	 */
	public function getRoles()
	{
		return $this->getState('roles',array());
	}

	/**
	 * @param array list of roles that this user is in.
	 */
	public function setRoles($value)
	{
		if($value===null)
			$value=array();
		if(!is_array($value))
			$value=array($value);
		$this->setState('roles',array_map('strtolower',$value),array());
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
		return $this->getState('returnUrl',Yii::app()->getRequest()->getScriptUrl());
	}

	/**
	 * @param string the URL that the user should be redirected to after login.
	 */
	public function setReturnUrl($value)
	{
		$this->setState('returnUrl',$value);
	}

	/**
	 * Returns the guest username.
	 * You may override this method to provide a different guest username (e.g. a localizable username).
	 * @return string the username for a guest user. Defaults to 'Guest'.
	 */
	protected function getGuestName()
	{
		return 'Guest';
	}

	/**
	 * @return boolean whether the current application user is a guest.
	 */
	public function getIsGuest()
	{
		return $this->getState('username')===null;
	}

	/**
	 * @param string role name
	 * @return whether the user is of the specified role
	 */
	public function isInRole($role)
	{
		return in_array($role,$this->getRoles());
	}

	/**
	 * Redirects the user browser to the login page.
	 * Before the redirection, the current URL will be kept in {@link getReturnUrl returnUrl}
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
			throw new CHttpException(401,Yii::t('yii##Login Required'));
	}

	/**
	 * Logs in a user.
	 * This method will first authenticate the user based on the specified
	 * username and password. It then populates the user with additional
	 * credential-related information that should be kept in session (e.g.
	 * username, roles). If the login duration is greater than 0, it will
	 * also save the login information in cookie so that the user can remain
	 * logged in even he closes the browser.
	 *
	 * Note, you have to set {@link allowAutoLogin} to true
	 * if you want to allow user to be authenticated based on the cookie information.
	 * Otherwise, the duration parameter will have no effect.
	 *
	 * @param string username
	 * @param string password
	 * @param integer number of seconds that the user can remain in logged-in status. Defaults to 0, meaning login till the user closes the browser.
	 * @return boolean whether the login is successful
	 */
	public function login($username,$password,$duration=0)
	{
		if($this->validate($username,$password))
		{
			$this->switchTo($username);
			if($duration>0)
			{
				if(!$this->allowAutoLogin)
					throw new CException(Yii::t('yii##{class}.allowAutoLogin must be set true in order to use cookie-based authentication.',
						array('{class}'=>get_class($this))));
				$cookie=new CHttpCookie($this->getSessionKeyPrefix(),'');
				$cookie->expire=time()+$duration;
				$this->saveToCookie($cookie);
				Yii::app()->getRequest()->getCookies()->add($cookie->name,$cookie);
			}
			return true;
		}
		else
			return false;
	}

	/**
	 * Logs out the current user.
	 * The session will be destroyed.
	 */
	public function logout()
	{
		$app=Yii::app();
		if($this->allowAutoLogin)
			$app->getRequest()->getCookies()->remove($this->getSessionKeyPrefix());
		$app->getSession()->destroy();
	}

	/**
	 * Validates if username and password are correct entries.
	 * Usually, this is accomplished by checking if the user database
	 * contains this (username, password) pair.
	 * @param string username
	 * @param string password
	 * @return boolean whether the validation succeeds
	 */
	public function validate($username,$password)
	{
		throw new CException(Yii::t('yii##You must implement {class}.validate() method in order to do user authentication.',
			array('class'=>get_class($this))));
	}

	/**
	 * Populates the current user object with the information related with the specified username.
	 * The default implementation only sets the username.
	 * You may override this method if you want to save more information in session (e.g. id, roles).
	 * @param string username
	 */
	public function switchTo($username)
	{
		$this->setUsername($username);
	}

	/**
	 * Returns a validation key that is used to strengthen the cookie-based authentication.
	 * The key will be saved in the cookie and validated with authentication is needed.
	 * Although the cookie-based authentication is still safe without a validation key,
	 * it is recommended that you provide a non-empty validation key for enhanced security.
	 * A good choice of validation key is a randomly generated token associated with the username
	 * and stored in the database.
	 * @param string the username whose validation key is to be generated.
	 * @return string the validation key associated with the user.
	 */
	protected function getValidationKey($username)
	{
		return '';
	}

	/**
	 * Populates the current user object with the information obtained from cookie.
	 * This method is used when automatic login ({@link allowAutoLogin}) is enabled.
	 * The user information is recovered from cookie using username, user IP address and a validation key.
	 * Sufficient security measures are used to prevent cookie data from being tampered.
	 * @see saveToCookie
	 */
	protected function restoreFromCookie()
	{
		$app=Yii::app();
		$cookie=$app->getRequest()->getCookies()->itemAt($this->getSessionKeyPrefix());
		if($cookie && !empty($cookie->value) && ($data=$app->getSecurityManager()->validateData($cookie->value))!==false)
		{
			$data=unserialize($data);
			if(isset($data[0],$data[1],$data[2]))
			{
				list($username,$address,$validationKey)=$data;
				if($address===$app->getRequest()->getUserHostAddress() && $this->getValidationKey($username)===$validationKey)
					$this->switchTo($username);
			}
		}
	}

	/**
	 * Saves necessary user data into a cookie.
	 * This method is used when automatic login ({@link allowAutoLogin}) is enabled.
	 * This method saves username, user IP address and a validation key to cookie.
	 * These information are used to do authentication next time when user visits the application.
	 * @param CHttpCookie the cookie to store the user auth information
	 * @see restoreFromCookie
	 */
	protected function saveToCookie($cookie)
	{
		$app=Yii::app();
		$data[0]=$this->getUsername();
		$data[1]=$app->getRequest()->getUserHostAddress();
		$data[2]=$this->getValidationKey($data[0]);
		$cookie->value=$app->getSecurityManager()->hashData(serialize($data));
	}

	/**
	 * @return string a prefix for the name of the session variables storing user session data.
	 */
	protected function getSessionKeyPrefix()
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
		$key=$this->getSessionKeyPrefix().$key;
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
		$key=$this->getSessionKeyPrefix().$key;
		if($value===$defaultValue)
			unset($_SESSION[$key]);
		else
			$_SESSION[$key]=$value;
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
