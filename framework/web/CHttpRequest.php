<?php
/**
 * CHttpRequest and CCookieCollection class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
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

	private $_pathInfo;
	private $_scriptFile;
	private $_scriptUrl;
	private $_hostInfo;
	private $_url;
	private $_baseUrl;
	private $_cookies;
	private $_preferredLanguage;

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
				$schema='https';
			else
				$schema='http';
			$segs=explode(':',$_SERVER['HTTP_HOST']);
			$url=$schema.'://'.$segs[0];
			$port=$_SERVER['SERVER_PORT'];
			if(($port!=80 && !$secure) || ($port!=443 && $secure))
				$url.=':'.$port;
			$this->_hostInfo=$url;
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
	 * @return string the relative URL for the application
	 * @see setScriptUrl
	 */
	public function getBaseUrl()
	{
		if($this->_baseUrl===null)
			$this->_baseUrl=rtrim(dirname($this->getScriptUrl()),'\\/');
		return $this->_baseUrl;
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
	 * @return string the relative URL of the entry script.
	 */
	public function getScriptUrl()
	{
		if($this->_scriptUrl!==null)
			return $this->_scriptUrl;
		else
		{
			if(isset($_SERVER['SCRIPT_NAME']))
				$this->_scriptUrl=$_SERVER['SCRIPT_NAME'];
			else
				throw new CException(Yii::t('yii##CHttpRequest is unable to determine the entry script URL.'));
			return $this->_scriptUrl;
		}
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
	 * @return string part of the request URL that is after the entry script and before the question mark.
	 * The starting and ending slashes are stripped off.
	 */
	public function getPathInfo()
	{
		if($this->_pathInfo===null)
			$this->_pathInfo=trim(isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : $this->guessPathInfo(), '/');
		return $this->_pathInfo;
	}

	/**
	 * Guesses the path info when <code>$_SERVER['PATH_INFO']</code> is not available.
	 *
	 * The default implementation guesses the path info in two ways.
	 * First, if <code>$_SERVER['PHP_SELF']</code> and <code>$_SERVER['SCRIPT_NAME']</code> are different,
	 * the path info is assumed to be the difference of the two.
	 * Second, if <code>$_SERVER['REQUEST_URI']</code> does not contain <code>$_SERVER['SCRIPT_NAME']</code>,
	 * it means some rewrite rule is mapping URLs to the entry script. In this case, the path info
	 * is assumed to be part of <code>$_SERVER['REQUEST_URI']</code>.
	 * If neither of the above methods works, you should override this method.
	 * @return string the path info.
	 */
	protected function guessPathInfo()
	{
		if($_SERVER['PHP_SELF']!==$_SERVER['SCRIPT_NAME'])
		{
			if(strpos($_SERVER['PHP_SELF'],$_SERVER['SCRIPT_NAME'])===0)
				return substr($_SERVER['PHP_SELF'],strlen($_SERVER['SCRIPT_NAME']));
		}
		else if(isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'],$_SERVER['SCRIPT_NAME'])!==0)
		{
			// REQUEST_URI doesn't contain SCRIPT_NAME, which means some rewrite rule is in effect
			$base=strtr(dirname($_SERVER['SCRIPT_NAME']),'\\','/');
			if(strpos($_SERVER['REQUEST_URI'],$base)===0)
			{
				$pathInfo=substr($_SERVER['REQUEST_URI'],strlen($base));
				if(($pos=strpos($pathInfo,'?'))!==false)
					return substr($pathInfo,0,$pos);
				else
					return $pathInfo;
			}
		}
		return '';
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
	 */
	public function redirect($url,$terminate=true)
	{
		if(strpos($url,'/')===0)
			$url=$this->getHostInfo().$url;
		header('Location: '.$url);
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
	 * @param array list of headers to be sent. Each array element represents a header string (e.g. 'Content-Type: text/plain').
	 * @param boolean whether to terminate the current application after calling this method
	 */
	public function sendFile($fileName,$content,$mimeType=null,$terminate=true)
	{
		static $defaultMimeTypes=array(
			'css'=>'text/css',
			'gif'=>'image/gif',
			'jpg'=>'image/jpeg',
			'jpeg'=>'image/jpeg',
			'htm'=>'text/html',
			'html'=>'text/html',
			'js'=>'javascript/js',
			'pdf'=>'application/pdf',
			'xls'=>'application/vnd.ms-excel',
		);

		if($mimeType===null)
		{
			$mimeType='text/plain';
			if(function_exists('mime_content_type'))
				$mimeType=mime_content_type($fileName);
			else if(($ext=strrchr($fileName,'.'))!==false)
			{
				$ext=strtolower(substr($ext,1));
				if(isset($defaultMimeTypes[$ext]))
					$mimeType=$defaultMimeTypes[$ext];
			}
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
			throw new CException(Yii::t('yii##CHttpCookieCollection can only hold CHttpCookie objects.'));
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
		setcookie($cookie->name,$value,$cookie->expire,$cookie->path,$cookie->domain,$cookie->secure);
	}

	/**
	 * Deletes a cookie.
	 * @param CHttpCookie cook to be deleted
	 */
	protected function removeCookie($cookie)
	{
		setcookie($cookie->name,null,0,$cookie->path,$cookie->domain,$cookie->secure);
	}
}
