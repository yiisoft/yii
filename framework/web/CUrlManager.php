<?php
/**
 * CUrlManager class file
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CUrlManager manages the URLs of Yii Web applications.
 *
 * It provides URL construction ({@link createUrl()}) as well as parsing ({@link parseUrl()}) functionality.
 *
 * URLs managed via CUrlManager can be in one of the following two formats,
 * by setting {@link setUrlFormat urlFormat} property:
 * <ul>
 * <li>'path' format: /path/to/EntryScript.php/name1/value1/name2/value2...</li>
 * <li>'get' format:  /path/to/EntryScript.php?name1=value1&name2=value2...</li>
 * </ul>
 *
 * When using 'path' format, CUrlManager uses a set of {@link setRules rules} to:
 * <ul>
 * <li>parse the requested URL into a route ('ControllerID/ActionID') and GET parameters;</li>
 * <li>create URLs based on the given route and GET parameters.</li>
 * </ul>
 *
 * A rule consists of a route and a pattern. The latter is used by CUrlManager to determine
 * which rule is used for parsing/creating URLs. A pattern is meant to match the path info
 * part of a URL. It may contain named parameters using the syntax '&lt;ParamName:RegExp&gt;'.
 *
 * When parsing a URL, a matching rule will extract the named parameters from the path info
 * and put them into the $_GET variable; when creating a URL, a matching rule will extract
 * the named parameters from $_GET and put them into the path info part of the created URL.
 *
 * If a pattern ends with '/*', it means additional GET parameters may be appended to the path
 * info part of the URL; otherwise, the GET parameters can only appear in the query string part.
 *
 * To specify URL rules, set the {@link setRules rules} property as an array of rules (pattern=>route).
 * For example,
 * <pre>
 * array(
 *     'articles'=>'article/list',
 *     'article/<id:\d+>/*'=>'article/read',
 * )
 * </pre>
 * Two rules are specified in the above:
 * <ul>
 * <li>The first rule says that if the user requests the URL '/path/to/index.php/articles',
 *   it should be treated as '/path/to/index.php/article/list'; and vice versa applies
 *   when constructing such a URL.</li>
 * <li>The second rule contains a named parameter 'id' which is specified using
 *   the &lt;ParamName:RegExp&gt; syntax. It says that if the user requests the URL
 *   '/path/to/index.php/article/13', it should be treated as '/path/to/index.php/article/read?id=13';
 *   and vice versa applies when constructing such a URL.</li>
 * </ul>
 *
 * The route part may contain references to named parameters defined in the pattern part.
 * This allows a rule to be applied to different routes based on matching criteria.
 * For example,
 * <pre>
 * array(
 *      '<_c:(post|comment)>/<id:\d+>/<_a:(create|update|delete)>'=>'<_c>/<_a>',
 *      '<_c:(post|comment)>/<id:\d+>'=>'<_c>/view',
 *      '<_c:(post|comment)>s/*'=>'<_c>/list',
 * )
 * </pre>
 * In the above, we use two named parameters '<_c>' and '<_a>' in the route part. The '<_c>'
 * parameter matches either 'post' or 'comment', while the '<_a>' parameter matches an action ID.
 *
 * Like normal rules, these rules can be used for both parsing and creating URLs.
 * For example, using the rules above, the URL '/index.php/post/123/create'
 * would be parsed as the route 'post/create' with GET parameter 'id' being 123.
 * And given the route 'post/list' and GET parameter 'page' being 2, we should get a URL
 * '/index.php/posts/page/2'.
 *
 * It is also possible to include hostname into the rules for parsing and creating URLs.
 * One may extract part of the hostname to be a GET parameter.
 * For example, the URL <code>http://admin.example.com/en/profile</code> may be parsed into GET parameters
 * <code>user=admin</code> and <code>lang=en</code>. On the other hand, rules with hostname may also be used to
 * create URLs with parameterized hostnames.
 *
 * In order to use parameterized hostnames, simply declare URL rules with host info, e.g.:
 * <pre>
 * array(
 *     'http://<user:\w+>.example.com/<lang:\w+>/profile' => 'user/profile',
 * )
 * </pre>
 *
 * Starting from version 1.1.8, one can write custom URL rule classes and use them for one or several URL rules.
 * For example,
 * <pre>
 * array(
 *   // a standard rule
 *   '<action:(login|logout)>' => 'site/<action>',
 *   // a custom rule using data in DB
 *   array(
 *     'class' => 'application.components.MyUrlRule',
 *     'connectionID' => 'db',
 *   ),
 * )
 * </pre>
 * Please note that the custom URL rule class should extend from {@link CBaseUrlRule} and
 * implement the following two methods,
 * <ul>
 *    <li>{@link CBaseUrlRule::createUrl()}</li>
 *    <li>{@link CBaseUrlRule::parseUrl()}</li>
 * </ul>
 *
 * CUrlManager is a default application component that may be accessed via
 * {@link CWebApplication::getUrlManager()}.
 *
 * @property string $baseUrl The base URL of the application (the part after host name and before query string).
 * If {@link showScriptName} is true, it will include the script name part.
 * Otherwise, it will not, and the ending slashes are stripped off.
 * @property string $urlFormat The URL format. Defaults to 'path'. Valid values include 'path' and 'get'.
 * Please refer to the guide for more details about the difference between these two formats.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web
 * @since 1.0
 */
class CUrlManager extends CApplicationComponent
{
	const CACHE_KEY='Yii.CUrlManager.rules';
	const GET_FORMAT='get';
	const PATH_FORMAT='path';

	/**
	 * @var array the URL rules (pattern=>route).
	 */
	public $rules=array();
	/**
	 * @var string the URL suffix used when in 'path' format.
	 * For example, ".html" can be used so that the URL looks like pointing to a static HTML page. Defaults to empty.
	 */
	public $urlSuffix='';
	/**
	 * @var bool whether to show entry script name in the constructed URL. Defaults to true.
	 */
	public $showScriptName=true;
	/**
	 * @var bool whether to append GET parameters to the path info part. Defaults to true.
	 * This property is only effective when {@link urlFormat} is 'path' and is mainly used when
	 * creating URLs. When it is true, GET parameters will be appended to the path info and
	 * separate from each other using slashes. If this is false, GET parameters will be in query part.
	 */
	public $appendParams=true;
	/**
	 * @var string the GET variable name for route. Defaults to 'r'.
	 */
	public $routeVar='r';
	/**
	 * @var bool whether routes are case-sensitive. Defaults to true. By setting this to false,
	 * the route in the incoming request will be turned to lower case first before further processing.
	 * As a result, you should follow the convention that you use lower case when specifying
	 * controller mapping ({@link CWebApplication::controllerMap}) and action mapping
	 * ({@link CController::actions}). Also, the directory names for organizing controllers should
	 * be in lower case.
	 */
	public $caseSensitive=true;
	/**
	 * @var bool whether the GET parameter values should match the corresponding
	 * sub-patterns in a rule before using it to create a URL. Defaults to false, meaning
	 * a rule will be used for creating a URL only if its route and parameter names match the given ones.
	 * If this property is set true, then the given parameter values must also match the corresponding
	 * parameter sub-patterns. Note that setting this property to true will degrade performance.
	 * @since 1.1.0
	 */
	public $matchValue=false;
	/**
	 * @var string the ID of the cache application component that is used to cache the parsed URL rules.
	 * Defaults to 'cache' which refers to the primary cache application component.
	 * Set this property to false if you want to disable caching URL rules.
	 */
	public $cacheID='cache';
	/**
	 * @var bool whether to enable strict URL parsing.
	 * This property is only effective when {@link urlFormat} is 'path'.
	 * If it is set true, then an incoming URL must match one of the {@link rules URL rules}.
	 * Otherwise, it will be treated as an invalid request and trigger a 404 HTTP exception.
	 * Defaults to false.
	 */
	public $useStrictParsing=false;
	/**
	 * @var string the class name or path alias for the URL rule instances. Defaults to 'CUrlRule'.
	 * If you change this to something else, please make sure that the new class must extend from
	 * {@link CBaseUrlRule} and have the same constructor signature as {@link CUrlRule}.
	 * It must also be serializable and autoloadable.
	 * @since 1.1.8
	 */
	public $urlRuleClass= 'CUrlRule';

	private $_urlFormat=self::GET_FORMAT;
	private $_rules=array();
	private $_baseUrl;


	/**
	 * Initializes the application component.
	 *
	 * @return void
	 */
	public function init()
	{
		parent::init();
		$this->processRules();
	}

	/**
	 * Processes the URL rules.
	 *
	 * @return void
	 */
	protected function processRules()
	{
		if(empty($this->rules) || $this->getUrlFormat()===self::GET_FORMAT)
			return;
		$hash = null;
		if($this->cacheID!==false && ($cache=Yii::app()->getComponent($this->cacheID))!==null)
		{
			$hash=md5(serialize($this->rules));
			if(($data=$cache->get(self::CACHE_KEY))!==false && isset($data[1]) && $data[1]===$hash)
			{
				$this->_rules=$data[0];
				return;
			}
		}
		foreach($this->rules as $pattern=>$route)
			$this->_rules[]=$this->createUrlRule($route,$pattern);
		if(isset($cache))
			$cache->set(self::CACHE_KEY,array($this->_rules,$hash));
	}

	/**
	 * Adds new URL rules.
	 * In order to make the new rules effective, this method must be called BEFORE
	 * {@link CWebApplication::processRequest}.
	 *
	 * @param array $rules new URL rules (pattern=>route).
	 * @param bool $append whether the new URL rules should be appended to the existing ones. If false,
	 * they will be inserted at the beginning.
	 *
	 * @since 1.1.4
	 *
	 * @return void
	 */
	public function addRules($rules,$append=true)
	{
		if ($append)
		{
			foreach($rules as $pattern=>$route)
				$this->_rules[]=$this->createUrlRule($route,$pattern);
		}
		else
		{
			$rules=array_reverse($rules);
			foreach($rules as $pattern=>$route)
				array_unshift($this->_rules, $this->createUrlRule($route,$pattern));
		}
	}

	/**
	 * Creates a URL rule instance.
	 * The default implementation returns a CUrlRule object.
	 *
	 * @param string|array $route the route part of the rule. This could be a string or an array
	 * @param string $pattern the pattern part of the rule
	 *
	 * @return array|CUrlRule the URL rule instance
	 *
	 * @since 1.1.0
	 */
	protected function createUrlRule($route,$pattern)
	{
		if(is_array($route) && isset($route['class']))
			return $route;
		else
		{
			$urlRuleClass=Yii::import($this->urlRuleClass,true);
			return new $urlRuleClass($route,$pattern);
		}
	}

	/**
	 * Constructs a URL.
	 * @param string $route the controller and the action (e.g. article/read)
	 * @param array $params list of GET parameters (name=>value). Both the name and value will be URL-encoded.
	 * If the name is '#', the corresponding value will be treated as an anchor
	 * and will be appended at the end of the URL.
	 * @param string $ampersand the token separating name-value pairs in the URL. Defaults to '&'.
	 * @return string the constructed URL
	 */
	public function createUrl($route,$params=array(),$ampersand='&')
	{
		unset($params[$this->routeVar]);
		foreach($params as $i=>$param)
			if($param===null)
				$params[$i]='';

		if(isset($params['#']))
		{
			$anchor='#'.$params['#'];
			unset($params['#']);
		}
		else
			$anchor='';
		$route=trim($route,'/');
		foreach($this->_rules as $i=>$rule)
		{
			if(is_array($rule))
				$this->_rules[$i]=$rule=Yii::createComponent($rule);
			if(($url=$rule->createUrl($this,$route,$params,$ampersand))!==false)
			{
				if($rule->hasHostInfo)
					return $url==='' ? '/'.$anchor : $url.$anchor;
				else
					return $this->getBaseUrl().'/'.$url.$anchor;
			}
		}
		return $this->createUrlDefault($route,$params,$ampersand).$anchor;
	}

	/**
	 * Creates a URL based on default settings.
	 * @param string $route the controller and the action (e.g. article/read)
	 * @param array $params list of GET parameters
	 * @param string $ampersand the token separating name-value pairs in the URL.
	 * @return string the constructed URL
	 */
	protected function createUrlDefault($route,$params,$ampersand)
	{
		if($this->getUrlFormat()===self::PATH_FORMAT)
		{
			$url=rtrim($this->getBaseUrl().'/'.$route,'/');
			if($this->appendParams)
			{
				$url=rtrim($url.'/'.$this->createPathInfo($params,'/','/'),'/');
				return $route==='' ? $url : $url.$this->urlSuffix;
			}
			else
			{
				if($route!=='')
					$url.=$this->urlSuffix;
				$query=$this->createPathInfo($params,'=',$ampersand);
				return $query==='' ? $url : $url.'?'.$query;
			}
		}
		else
		{
			$url=$this->getBaseUrl();
			if(!$this->showScriptName)
				$url.='/';
			if($route!=='')
			{
				$url.='?'.$this->routeVar.'='.$route;
				if(($query=$this->createPathInfo($params,'=',$ampersand))!=='')
					$url.=$ampersand.$query;
			}
			elseif(($query=$this->createPathInfo($params,'=',$ampersand))!=='')
				$url.='?'.$query;
			return $url;
		}
	}

	/**
	 * Parses the user request.
	 * @param CHttpRequest $request the request application component
	 * @return string the route (controllerID/actionID) and perhaps GET parameters in path format.
	 * @throws CException
	 * @throws CHttpException
	 */
	public function parseUrl($request)
	{
		if($this->getUrlFormat()===self::PATH_FORMAT)
		{
			$rawPathInfo=$request->getPathInfo();
			$pathInfo=$this->removeUrlSuffix($rawPathInfo,$this->urlSuffix);
			foreach($this->_rules as $i=>$rule)
			{
				if(is_array($rule))
					$this->_rules[$i]=$rule=Yii::createComponent($rule);
				if(($r=$rule->parseUrl($this,$request,$pathInfo,$rawPathInfo))!==false)
					return isset($_GET[$this->routeVar]) ? $_GET[$this->routeVar] : $r;
			}
			if($this->useStrictParsing)
				throw new CHttpException(404,Yii::t('yii','Unable to resolve the request "{route}".',
					array('{route}'=>$pathInfo)));
			else
				return $pathInfo;
		}
		elseif(isset($_GET[$this->routeVar]))
			return $_GET[$this->routeVar];
		elseif(isset($_POST[$this->routeVar]))
			return $_POST[$this->routeVar];
		else
			return '';
	}

	/**
	 * Parses a path info into URL segments and saves them to $_GET and $_REQUEST.
	 *
	 * @param string $pathInfo path info
	 *
	 * @return void
	 */
	public function parsePathInfo($pathInfo)
	{
		if($pathInfo==='')
			return;
		$segs=explode('/',$pathInfo.'/');
		$n=count($segs);
		for($i=0;$i<$n-1;$i+=2)
		{
			$key=$segs[$i];
			if($key==='') continue;
			$value=$segs[$i+1];
			if(($pos=strpos($key,'['))!==false && ($m=preg_match_all('/\[(.*?)\]/',$key,$matches))>0)
			{
				$name=substr($key,0,$pos);
				for($j=$m-1;$j>=0;--$j)
				{
					if($matches[1][$j]==='')
						$value=array($value);
					else
						$value=array($matches[1][$j]=>$value);
				}
				if(isset($_GET[$name]) && is_array($_GET[$name]))
					$value=CMap::mergeArray($_GET[$name],$value);
				$_REQUEST[$name]=$_GET[$name]=$value;
			}
			else
				$_REQUEST[$key]=$_GET[$key]=$value;
		}
	}

	/**
	 * Creates a path info based on the given parameters.
	 * @param array $params list of GET parameters
	 * @param string $equal the separator between name and value
	 * @param string $ampersand the separator between name-value pairs
	 * @param string $key this is used internally.
	 * @return string the created path info
	 */
	public function createPathInfo($params,$equal,$ampersand, $key=null)
	{
		$pairs = array();
		foreach($params as $k => $v)
		{
			if ($key!==null)
				$k = $key.'['.$k.']';

			if (is_array($v))
				$pairs[]=$this->createPathInfo($v,$equal,$ampersand, $k);
			else
				$pairs[]=urlencode($k).$equal.urlencode($v);
		}
		return implode($ampersand,$pairs);
	}

	/**
	 * Removes the URL suffix from path info.
	 * @param string $pathInfo path info part in the URL
	 * @param string $urlSuffix the URL suffix to be removed
	 * @return string path info with URL suffix removed.
	 */
	public function removeUrlSuffix($pathInfo,$urlSuffix)
	{
		if($urlSuffix!=='' && substr($pathInfo,-strlen($urlSuffix))===$urlSuffix)
			return substr($pathInfo,0,-strlen($urlSuffix));
		else
			return $pathInfo;
	}

	/**
	 * Returns the base URL of the application.
	 * @return string the base URL of the application (the part after host name and before query string).
	 * If {@link showScriptName} is true, it will include the script name part.
	 * Otherwise, it will not, and the ending slashes are stripped off.
	 */
	public function getBaseUrl()
	{
		if($this->_baseUrl!==null)
			return $this->_baseUrl;
		else
		{
			if($this->showScriptName)
				$this->_baseUrl=Yii::app()->getRequest()->getScriptUrl();
			else
				$this->_baseUrl=Yii::app()->getRequest()->getBaseUrl();
			return $this->_baseUrl;
		}
	}

	/**
	 * Sets the base URL of the application (the part after host name and before query string).
	 * This method is provided in case the {@link baseUrl} cannot be determined automatically.
	 * The ending slashes should be stripped off. And you are also responsible to remove the script name
	 * if you set {@link showScriptName} to be false.
	 *
	 * @param string $value the base URL of the application
	 *
	 * @since 1.1.1
	 *
	 * @return void
	 */
	public function setBaseUrl($value)
	{
		$this->_baseUrl=$value;
	}

	/**
	 * Returns the URL format.
	 * @return string the URL format. Defaults to 'path'. Valid values include 'path' and 'get'.
	 * Please refer to the guide for more details about the difference between these two formats.
	 */
	public function getUrlFormat()
	{
		return $this->_urlFormat;
	}

	/**
	 * Sets the URL format.
	 *
	 * @param string $value the URL format. It must be either 'path' or 'get'.
	 *
	 * @throws CException
	 *
	 * @return void
	 */
	public function setUrlFormat($value)
	{
		if($value===self::PATH_FORMAT || $value===self::GET_FORMAT)
			$this->_urlFormat=$value;
		else
			throw new CException(Yii::t('yii','CUrlManager.UrlFormat must be either "path" or "get".'));
	}
}
