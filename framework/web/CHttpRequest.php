<?php
/**
 * CHttpRequest and CCookieCollection class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */


/**
 * CHttpRequest encapsulates the $_SERVER variable and resolves its inconsistency among different Web servers.
 *
 * CHttpRequest also manages the cookies sent from and sent to the user.
 * By setting {@link enableCookieValidation} to true,
 * cookies sent from the user will be validated to see if they are tampered.
 * The property {@link getCookies cookies} returns the collection of cookies.
 * For more details, see {@link CCookieCollection}.
 *
 * CHttpRequest is a default application component loaded by {@link CWebApplication}. It can be
 * accessed via {@link CWebApplication::getRequest()}.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.web
 * @since 1.0
 */
class CHttpRequest extends CApplicationComponent
{
	/**
	 * @var boolean whether cookies should be validated to ensure they are not tampered. Defaults to false.
	 */
	public $enableCookieValidation=false;
	/**
	 * @var boolean whether to enable CSRF (Cross-Site Request Forgery) validation. Defaults to false.
	 * By setting this property to true, forms submitted to an Yii Web application must be originated
	 * from the same application. If not, a 400 HTTP exception will be raised.
	 * Note, this feature requires that the user client accepts cookie.
	 * You also need to use {@link CHtml::form} or {@link CHtml::statefulForm} to generate
	 * the needed HTML forms in your pages.
	 * @see http://freedom-to-tinker.com/sites/default/files/csrf.pdf
	 */
	public $enableCsrfValidation=false;
	/**
	 * @var string the name of the token used to prevent CSRF. Defaults to 'YII_CSRF_TOKEN'.
	 * This property is effectively only when {@link enableCsrfValidation} is true.
	 */
	public $csrfTokenName='YII_CSRF_TOKEN';
	/**
	 * @var array the property values (in name-value pairs) used to initialize the CSRF cookie.
	 * Any property of {@link CHttpCookie} may be initialized.
	 * This property is effectively only when {@link enableCsrfValidation} is true.
	 */
	public $csrfCookie;

	private $_requestUri;
	private $_pathInfo;
	private $_scriptFile;
	private $_scriptUrl;
	private $_hostInfo;
	private $_url;
	private $_baseUrl;
	private $_cookies;
	private $_preferredLanguage;
	private $_csrfToken;

	/**
	 * Initializes the application component.
	 * This method overrides the parent implementation by preprocessing
	 * the user request data.
	 */
	public function init()
	{
		parent::init();
		$this->normalizeRequest();
	}

	/**
	 * Normalizes the request data.
	 * This method strips off slashes in request data if get_magic_quotes_gpc() returns true.
	 * It also performs CSRF validation if {@link enableCsrfValidation} is true.
	 */
	protected function normalizeRequest()
	{
		// normalize request
		if(get_magic_quotes_gpc())
		{
			if(isset($_GET))
				$_GET=$this->stripSlashes($_GET);
			if(isset($_POST))
				$_POST=$this->stripSlashes($_POST);
			if(isset($_REQUEST))
				$_REQUEST=$this->stripSlashes($_REQUEST);
			if(isset($_COOKIE))
				$_COOKIE=$this->stripSlashes($_COOKIE);
		}

		if($this->enableCsrfValidation)
			Yii::app()->attachEventHandler('onBeginRequest',array($this,'validateCsrfToken'));
	}


	/**
	 * Strips slashes from input data.
	 * This method is applied when magic quotes is enabled.
	 * @param mixed input data to be processed
	 * @return mixed processed data
	 */
	public function stripSlashes(&$data)
	{
		return is_array($data)?array_map(array($this,'stripSlashes'),$data):stripslashes($data);
	}

	/**
	 * Returns the named GET or POST parameter value.
	 * If the GET or POST parameter does not exist, the second parameter to this method will be returned.
	 * If both GET and POST contains such a named parameter, the GET parameter takes precedence.
	 * @param string the GET parameter name
	 * @param mixed the default parameter value if the GET parameter does not exist.
	 * @return mixed the GET parameter value
	 * @since 1.0.4
	 * @see getQuery
	 * @see getPost
	 */
	public function getParam($name,$defaultValue=null)
	{
		return isset($_GET[$name]) ? $_GET[$name] : (isset($_POST[$name]) ? $_POST[$name] : $defaultValue);
	}

	/**
	 * Returns the named GET parameter value.
	 * If the GET parameter does not exist, the second parameter to this method will be returned.
	 * @param string the GET parameter name
	 * @param mixed the default parameter value if the GET parameter does not exist.
	 * @return mixed the GET parameter value
	 * @since 1.0.4
	 * @see getPost
	 * @see getParam
	 */
	public function getQuery($name,$defaultValue=null)
	{
		return isset($_GET[$name]) ? $_GET[$name] : $defaultValue;
	}

	/**
	 * Returns the named POST parameter value.
	 * If the POST parameter does not exist, the second parameter to this method will be returned.
	 * @param string the POST parameter name
	 * @param mixed the default parameter value if the POST parameter does not exist.
	 * @return mixed the POST parameter value
	 * @since 1.0.4
	 * @see getParam
	 * @see getQuery
	 */
	public function getPost($name,$defaultValue=null)
	{
		return isset($_POST[$name]) ? $_POST[$name] : $defaultValue;
	}

	/**
	 * @return string part of the request URL after the host info.
	 * It consists of the following parts:
	 * <ul>
	 * <li>{@link getScriptUrl scriptUrl}</li>
	 * <li>{@link getPathInfo pathInfo}</li>
	 * <li>{@link getQueryString queryString}</li>
	 * </ul>
	 */
	public function getUrl()
	{
		if($this->_url!==null)
			return $this->_url;
		else
		{
			if(isset($_SERVER['REQUEST_URI']))
				$this->_url=$_SERVER['REQUEST_URI'];
			else
			{
				$this->_url=$this->getScriptUrl();
				if(($pathInfo=$this->getPathInfo())!=='')
					$this->_url.='/'.$pathInfo;
				if(($queryString=$this->getQueryString())!=='')
					$this->_url.='?'.$queryString;
			}
			return $this->_url;
		}
	}

	/**
	 * Returns the schema and host part of the application URL.
	 * The returned URL does not have an ending slash.
	 * By default this is determined based on the user request information.
	 * You may explicitly specify it by setting the {@link setHostInfo hostInfo} property.
	 * @param string schema to use (e.g. http, https). If empty, the schema used for the current request will be used.
	 * @return string schema and hostname part (with port number if needed) of the request URL (e.g. http://www.yiiframework.com)
	 * @see setHostInfo
	 */
	public function getHostInfo($schema='')
	{
		if($this->_hostInfo===null)
		{
			if($secure=$this->getIsSecureConnection())
				$http='https';
			else
				$http='http';
			if(isset($_SERVER['HTTP_HOST']))
				$this->_hostInfo=$http.'://'.$_SERVER['HTTP_HOST'];
			else
			{
				$this->_hostInfo=$http.'://'.$_SERVER['SERVER_NAME'];
				$port=$_SERVER['SERVER_PORT'];
				if(($port!=80 && !$secure) || ($port!=443 && $secure))
					$this->_hostInfo.=':'.$port;
			}
		}
		if($schema!=='' && ($pos=strpos($this->_hostInfo,':'))!==false)
			return $schema.substr($this->_hostInfo,$pos);
		else
			return $this->_hostInfo;
	}

	/**
	 * Sets the schema and host part of the application URL.
	 * This setter is provided in case the schema and hostname cannot be determined
	 * on certain Web servers.
	 * @param string the schema and host part of the application URL.
	 */
	public function setHostInfo($value)
	{
		$this->_hostInfo=rtrim($value,'/');
	}

	/**
	 * Returns the relative URL for the application.
	 * This is similar to {@link getScriptUrl scriptUrl} except that
	 * it does not have the script file name, and the ending slashes are stripped off.
	 * @param boolean whether to return an absolute URL. Defaults to false, meaning returning a relative one.
	 * This parameter has been available since 1.0.2.
	 * @return string the relative URL for the application
	 * @see setScriptUrl
	 */
	public function getBaseUrl($absolute=false)
	{
		if($this->_baseUrl===null)
			$this->_baseUrl=rtrim(dirname($this->getScriptUrl()),'\\/');
		return $absolute ? $this->getHostInfo() . $this->_baseUrl : $this->_baseUrl;
	}

	/**
	 * Sets the relative URL for the application.
	 * By default the URL is determined based on the entry script URL.
	 * This setter is provided in case you want to change this behavior.
	 * @param string the relative URL for the application
	 */
	public function setBaseUrl($value)
	{
		$this->_baseUrl=$value;
	}

	/**
	 * Returns the relative URL of the entry script.
	 * The implementation of this method referenced Zend_Controller_Request_Http in Zend Framework.
	 * @return string the relative URL of the entry script.
	 */
	public function getScriptUrl()
	{
		if($this->_scriptUrl===null)
		{
			$scriptName=basename($_SERVER['SCRIPT_FILENAME']);
			if(basename($_SERVER['SCRIPT_NAME'])===$scriptName)
				$this->_scriptUrl=$_SERVER['SCRIPT_NAME'];
			else if(basename($_SERVER['PHP_SELF'])===$scriptName)
				$this->_scriptUrl=$_SERVER['PHP_SELF'];
			else if(isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME'])===$scriptName)
				$this->_scriptUrl=$_SERVER['ORIG_SCRIPT_NAME'];
			else if(($pos=strpos($_SERVER['PHP_SELF'],'/'.$scriptName))!==false)
				$this->_scriptUrl=substr($_SERVER['SCRIPT_NAME'],0,$pos).'/'.$scriptName;
			else
				throw new CException(Yii::t('yii','CHttpRequest is unable to determine the entry script URL.'));
		}
		return $this->_scriptUrl;
	}

	/**
	 * Sets the relative URL for the application entry script.
	 * This setter is provided in case the entry script URL cannot be determined
	 * on certain Web servers.
	 * @param string the relative URL for the application entry script.
	 */
	public function setScriptUrl($value)
	{
		$this->_scriptUrl='/'.trim($value,'/');
	}

	/**
	 * Returns the path info of the currently requested URL.
	 * This refers to the part that is after the entry script and before the question mark.
	 * The starting and ending slashes are stripped off.
	 * @return string part of the request URL that is after the entry script and before the question mark.
	 * @throws CException if the request URI cannot be determined due to improper server configuration
	 */
	public function getPathInfo()
	{
		if($this->_pathInfo===null)
		{
			$requestUri=$this->getRequestUri();
			$scriptUrl=$this->getScriptUrl();
			$baseUrl=$this->getBaseUrl();
			if(strpos($requestUri,$scriptUrl)===0)
				$pathInfo=substr($requestUri,strlen($scriptUrl));
			else if($baseUrl==='' || strpos($requestUri,$baseUrl)===0)
				$pathInfo=substr($requestUri,strlen($baseUrl));
			else if(strpos($_SERVER['PHP_SELF'],$scriptUrl)===0)
				$pathInfo=substr($_SERVER['PHP_SELF'],strlen($scriptUrl));
			else
				throw new CException(Yii::t('yii','CHttpRequest is unable to determine the path info of the request.'));

			if(($pos=strpos($pathInfo,'?'))!==false)
				$pathInfo=substr($pathInfo,0,$pos);

			$this->_pathInfo=trim($pathInfo,'/');
		}
		return $this->_pathInfo;
	}

	/**
	 * Returns the request URI portion for the currently requested URL.
	 * This refers to the portion that is after the {@link hostInfo host info} part.
	 * It includes the {@link queryString query string} part if any.
	 * The implementation of this method referenced Zend_Controller_Request_Http in Zend Framework.
	 * @return string the request URI portion for the currently requested URL.
	 * @throws CException if the request URI cannot be determined due to improper server configuration
	 * @since 1.0.1
	 */
	public function getRequestUri()
	{
		if($this->_requestUri===null)
		{
			if(isset($_SERVER['HTTP_X_REWRITE_URL'])) // IIS
				$this->_requestUri=$_SERVER['HTTP_X_REWRITE_URL'];
			else if(isset($_SERVER['REQUEST_URI']))
			{
				$this->_requestUri=$_SERVER['REQUEST_URI'];
				if(strpos($this->_requestUri,$_SERVER['HTTP_HOST'])!==false)
					$this->_requestUri=preg_replace('/^\w+:\/\/[^\/]+/','',$this->_requestUri);
			}
			else if(isset($_SERVER['ORIG_PATH_INFO']))  // IIS 5.0 CGI
			{
				$this->_requestUri=$_SERVER['ORIG_PATH_INFO'];
				if(!empty($_SERVER['QUERY_STRING']))
					$this->_requestUri.='?'.$_SERVER['QUERY_STRING'];
			}
			else
				throw new CException(Yii::t('yii','CHttpRequest is unable to determine the request URI.'));
		}

		return $this->_requestUri;
	}

	/**
	 * @return string part of the request URL that is after the question mark
	 */
	public function getQueryString()
	{
		return isset($_SERVER['QUERY_STRING'])?$_SERVER['QUERY_STRING']:'';
	}

	/**
	 * @return boolean if the request is sent via secure channel (https)
	 */
	public function getIsSecureConnection()
	{
	    return isset($_SERVER['HTTPS']) && !strcasecmp($_SERVER['HTTPS'],'on');
	}

	/**
	 * @return string request type, such as GET, POST, HEAD, PUT, DELETE.
	 */
	public function getRequestType()
	{
		return strtoupper(isset($_SERVER['REQUEST_METHOD'])?$_SERVER['REQUEST_METHOD']:'GET');
	}

	/**
	 * @return boolean whether this is POST request.
	 */
	public function getIsPostRequest()
	{
		return !strcasecmp($_SERVER['REQUEST_METHOD'],'POST');
	}

	/**
	 * @return boolean whether this is an AJAX (XMLHttpRequest) request.
	 */
	public function getIsAjaxRequest()
	{
		return isset($_SERVER['HTTP_X_REQUESTED_WITH'])?$_SERVER['HTTP_X_REQUESTED_WITH']==='XMLHttpRequest' : false;
	}

	/**
	 * @return string server name
	 */
	public function getServerName()
	{
		return $_SERVER['SERVER_NAME'];
	}

	/**
	 * @return integer server port number
	 */
	public function getServerPort()
	{
		return $_SERVER['SERVER_PORT'];
	}

	/**
	 * @return string URL referrer, null if not present
	 */
	public function getUrlReferrer()
	{
		return isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:null;
	}

	/**
	 * @return string user agent
	 */
	public function getUserAgent()
	{
		return $_SERVER['HTTP_USER_AGENT'];
	}

	/**
	 * @return string user IP address
	 */
	public function getUserHostAddress()
	{
		return isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:'127.0.0.1';
	}

	/**
	 * @return string user host name, null if cannot be determined
	 */
	public function getUserHost()
	{
		return isset($_SERVER['REMOTE_HOST'])?$_SERVER['REMOTE_HOST']:null;
	}

	/**
	 * @return string entry script file path (processed w/ realpath())
	 */
	public function getScriptFile()
	{
		if($this->_scriptFile!==null)
			return $this->_scriptFile;
		else
			return $this->_scriptFile=realpath($_SERVER['SCRIPT_FILENAME']);
	}

	/**
	 * @return array user browser capabilities.
	 * @see http://www.php.net/manual/en/function.get-browser.php
	 */
	public function getBrowser()
	{
		return get_browser();
	}

	/**
	 * @return string user browser accept types
	 */
	public function getAcceptTypes()
	{
		return $_SERVER['HTTP_ACCEPT'];
	}

	/**
	 * Returns the cookie collection.
	 * The result can be used like an associative array. Adding {@link CHttpCookie} objects
	 * to the collection will send the cookies to the client; and removing the objects
	 * from the collection will delete those cookies on the client.
	 * @return CCookieCollection the cookie collection.
	 */
	public function getCookies()
	{
		if($this->_cookies!==null)
			return $this->_cookies;
		else
			return $this->_cookies=new CCookieCollection($this);
	}

	/**
	 * Redirects the browser to the specified URL.
	 * @param string URL to be redirected to. If the URL is a relative one, the base URL of
	 * the application will be inserted at the beginning.
	 * @param boolean whether to terminate the current application
	 * @param integer the HTTP status code. Defaults to 302. See {@link http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html}
	 * for details about HTTP status code. This parameter has been available since version 1.0.4.
	 */
	public function redirect($url,$terminate=true,$statusCode=302)
	{
		if(strpos($url,'/')===0)
			$url=$this->getHostInfo().$url;
		header('Location: '.$url, true, $statusCode);
		if($terminate)
			Yii::app()->end();
	}

	/**
	 * @return string the user preferred language.
	 * The returned language ID will be canonicalized using {@link CLocale::getCanonicalID}.
	 * This method returns false if the user does not have language preference.
	 */
	public function getPreferredLanguage()
	{
		if($this->_preferredLanguage===null)
		{
			if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && ($n=preg_match_all('/([\w\-_]+)\s*(;\s*q\s*=\s*(\d*\.\d*))?/',$_SERVER['HTTP_ACCEPT_LANGUAGE'],$matches))>0)
			{
				$languages=array();
				for($i=0;$i<$n;++$i)
					$languages[$matches[1][$i]]=empty($matches[3][$i]) ? 1.0 : floatval($matches[3][$i]);
				arsort($languages);
				foreach($languages as $language=>$pref)
					return $this->_preferredLanguage=CLocale::getCanonicalID($language);
			}
			return $this->_preferredLanguage=false;
		}
		return $this->_preferredLanguage;
	}

	/**
	 * Sends a file to user.
	 * @param string file name
	 * @param string content to be set.
	 * @param string mime type of the content. If null, it will be guessed automatically based on the given file name.
	 * @param boolean whether to terminate the current application after calling this method
	 */
	public function sendFile($fileName,$content,$mimeType=null,$terminate=true)
	{
		if($mimeType===null)
		{
			if(($mimeType=CFileHelper::getMimeTypeByExtension($fileName))===null)
				$mimeType='text/plain';
		}
		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header("Content-type: $mimeType");
		header('Content-Length: '.strlen($content));
		header("Content-Disposition: attachment; filename=\"$fileName\"");
		header('Content-Transfer-Encoding: binary');
		echo $content;
		if($terminate)
			Yii::app()->end();
	}

	/**
	 * Returns the random token used to perform CSRF validation.
	 * The token will be read from cookie first. If not found, a new token
	 * will be generated.
	 * @return string the random token for CSRF validation.
	 * @see enableCsrfValidation
	 */
	public function getCsrfToken()
	{
		if($this->_csrfToken===null)
		{
			$cookie=$this->getCookies()->itemAt($this->csrfTokenName);
			if(!$cookie || ($this->_csrfToken=$cookie->value)==null)
			{
				$cookie=$this->createCsrfCookie();
				$this->_csrfToken=$cookie->value;
				$this->getCookies()->add($cookie->name,$cookie);
			}
		}

		return $this->_csrfToken;
	}

	/**
	 * Creates a cookie with a randomly generated CSRF token.
	 * Initial values specified in {@link csrfCookie} will be applied
	 * to the generated cookie.
	 * @return CHttpCookie the generated cookie
	 * @see enableCsrfValidation
	 */
	protected function createCsrfCookie()
	{
		$cookie=new CHttpCookie($this->csrfTokenName,sha1(uniqid(rand(),true)));
		if(is_array($this->csrfCookie))
		{
			foreach($this->csrfCookie as $name=>$value)
				$cookie->$name=$value;
		}
		return $cookie;
	}

	/**
	 * Performs the CSRF validation.
	 * This is the event handler responding to {@link CApplication::onBeginRequest}.
	 * The default implementation will compare the CSRF token obtained
	 * from a cookie and from a POST field. If they are different, a CSRF attack is detected.
	 * @param CEvent event parameter
	 * @throws CHttpException if the validation fails
	 * @since 1.0.4
	 */
	public function validateCsrfToken($event)
	{
		if($this->getIsPostRequest())
		{
			// only validate POST requests
			$cookies=$this->getCookies();
			if($cookies->contains($this->csrfTokenName) && isset($_POST[$this->csrfTokenName]))
			{
				$tokenFromCookie=$cookies->itemAt($this->csrfTokenName)->value;
				$tokenFromPost=$_POST[$this->csrfTokenName];
				$valid=$tokenFromCookie===$tokenFromPost;
			}
			else
				$valid=false;
			if(!$valid)
				throw new CHttpException(400,Yii::t('yii','The CSRF token could not be verified.'));
		}
	}
}


/**
 * CCookieCollection implements a collection class to store cookies.
 *
 * You normally access it via {@link CHttpRequest::getCookies()}.
 *
 * Since CCookieCollection extends from {@link CMap}, it can be used
 * like an associative array as follows:
 * <pre>
 * $cookies[$name]=new CHttpCookie($name,$value); // sends a cookie
 * $value=$cookies[$name]->value; // reads a cookie value
 * unset($cookies[$name]);  // removes a cookie
 * </pre>
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.web
 * @since 1.0
 */
class CCookieCollection extends CMap
{
	private $_request;
	private $_initialized=false;

	/**
	 * Constructor.
	 * @param CHttpRequest owner of this collection.
	 */
	public function __construct(CHttpRequest $request)
	{
		$this->_request=$request;
		$this->copyfrom($this->getCookies());
		$this->_initialized=true;
	}

	/**
	 * @return CHttpRequest the request instance
	 */
	public function getRequest()
	{
		return $this->_request;
	}

	/**
	 * @return array list of validated cookies
	 */
	protected function getCookies()
	{
		$cookies=array();
		if($this->_request->enableCookieValidation)
		{
			$sm=Yii::app()->getSecurityManager();
			foreach($_COOKIE as $name=>$value)
			{
				if(($value=$sm->validateData($value))!==false)
					$cookies[$name]=new CHttpCookie($name,$value);
			}
		}
		else
		{
			foreach($_COOKIE as $name=>$value)
				$cookies[$name]=new CHttpCookie($name,$value);
		}
		return $cookies;
	}

	/**
	 * Inserts an item at the specified position.
	 * This overrides the parent implementation by performing additional
	 * operations for each newly added CHttpCookie object.
	 * @param integer the specified position.
	 * @param mixed new item
	 * @throws CException if the item to be inserted is not a CHttpCookie object.
	 */
	public function add($name,$cookie)
	{
		if($cookie instanceof CHttpCookie)
		{
			$this->remove($name);
			parent::add($name,$cookie);
			if($this->_initialized)
				$this->addCookie($cookie);
		}
		else
			throw new CException(Yii::t('yii','CHttpCookieCollection can only hold CHttpCookie objects.'));
	}

	/**
	 * Removes an item at the specified position.
	 * This overrides the parent implementation by performing additional
	 * cleanup work when removing a TCookie object.
	 * @param integer the index of the item to be removed.
	 * @return mixed the removed item.
	 */
	public function remove($name)
	{
		if(($cookie=parent::remove($name))!==null)
		{
			if($this->_initialized)
				$this->removeCookie($cookie);
		}
		return $cookie;
	}

	/**
	 * Sends a cookie.
	 * @param CHttpCookie cook to be sent
	 */
	protected function addCookie($cookie)
	{
		$value=$cookie->value;
		if($this->_request->enableCookieValidation)
			$value=Yii::app()->getSecurityManager()->hashData($value);
		if(version_compare(PHP_VERSION,'5.2.0','>='))
			setcookie($cookie->name,$value,$cookie->expire,$cookie->path,$cookie->domain,$cookie->secure,$cookie->httpOnly);
		else
			setcookie($cookie->name,$value,$cookie->expire,$cookie->path,$cookie->domain,$cookie->secure);
	}

	/**
	 * Deletes a cookie.
	 * @param CHttpCookie cook to be deleted
	 */
	protected function removeCookie($cookie)
	{
		if(version_compare(PHP_VERSION,'5.2.0','>='))
			setcookie($cookie->name,null,0,$cookie->path,$cookie->domain,$cookie->secure,$cookie->httpOnly);
		else
			setcookie($cookie->name,null,0,$cookie->path,$cookie->domain,$cookie->secure);
	}
}
