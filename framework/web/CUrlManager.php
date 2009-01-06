<?php
/**
 * CUrlManager class file
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
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
 * CUrlManager is a default application component that may be accessed via
 * {@link CWebApplication::getUrlManager()}.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.web
 * @since 1.0
 */
class CUrlManager extends CApplicationComponent
{
	const CACHE_KEY='CUrlManager.rules';
	const GET_FORMAT='get';
	const PATH_FORMAT='path';

	/**
	 * @var string the URL suffix used when in 'path' format.
	 * For example, ".html" can be used so that the URL looks like pointing to a static HTML page. Defaults to empty.
	 */
	public $urlSuffix='';
	/**
	 * @var boolean whether to show entry script name in the constructed URL. Defaults to true.
	 */
	public $showScriptName=true;
	/**
	 * @var string the GET variable name for route. Defaults to 'r'.
	 */
	public $routeVar='r';
	/**
	 * @var boolean whether routes are case-sensitive. Defaults to true. By setting this to false,
	 * the route in the incoming request will be turned to lower case first before further processing.
	 * As a result, you should follow the convent that you use lower case when specifying
	 * controller mapping ({@link CWebApplication::controllerMap}) and action mapping
	 * ({@link CController::actions}). Also, the directory names for organizing controllers should
	 * be in lower case.
	 * @since 1.0.1
	 */
	public $caseSensitive=true;

	private $_urlFormat=self::GET_FORMAT;
	private $_rules=array();
	private $_groups=array();
	private $_baseUrl;


	/**
	 * Initializes the application component.
	 */
	public function init()
	{
		parent::init();
		$this->processRules();
	}

	/**
	 * Processes the URL rules.
	 */
	protected function processRules()
	{
		if(empty($this->_rules) || $this->getUrlFormat()===self::GET_FORMAT)
			return;
		if(($cache=Yii::app()->getCache())!==null)
		{
			$hash=md5(serialize($this->_rules));
			if(($data=$cache->get(self::CACHE_KEY))!==false && isset($data[1]) && $data[1]===$hash)
			{
				$this->_groups=$data[0];
				return;
			}
		}
		foreach($this->_rules as $pattern=>$route)
			$this->_groups[$route][]=new CUrlRule($route,$pattern);
		if($cache!==null)
			$cache->set(self::CACHE_KEY,array($this->_groups,$hash));
	}

	/**
	 * @return array the URL rules
	 */
	public function getRules()
	{
		return $this->_rules;
	}

	/**
	 * Sets the URL rules.
	 * @param array the URL rules (pattern=>route)
	 */
	public function setRules($value)
	{
		if($this->_rules===array())
			$this->_rules=$value;
		else
			$this->_rules=array_merge($this->_rules,$value);
	}

	/**
	 * Constructs a URL.
	 * @param string the controller and the action (e.g. article/read)
	 * @param array list of GET parameters (name=>value). Both the name and value will be URL-encoded.
	 * If the name is '#', the corresponding value will be treated as an anchor
	 * and will be appended at the end of the URL. This anchor feature has been available since version 1.0.1.
	 * @param string the token separating name-value pairs in the URL. Defaults to '&'.
	 * @return string the constructed URL
	 */
	public function createUrl($route,$params=array(),$ampersand='&')
	{
		unset($params[$this->routeVar]);
		if(isset($params['#']))
		{
			$anchor='#'.$params['#'];
			unset($params['#']);
		}
		else
			$anchor='';
		if(isset($this->_groups[$route]))
		{
			foreach($this->_groups[$route] as $rule)
			{
				if(($url=$rule->createUrl($params,$this->urlSuffix,$ampersand))!==false)
					return $this->getBaseUrl().'/'.$url.$anchor;
			}
		}
		return $this->createUrlDefault($route,$params,$ampersand).$anchor;
	}

	/**
	 * Contructs a URL based on default settings.
	 * @param string the controller and the action (e.g. article/read)
	 * @param array list of GET parameters
	 * @param string the token separating name-value pairs in the URL.
	 * @return string the constructed URL
	 */
	protected function createUrlDefault($route,$params,$ampersand)
	{
		if($this->getUrlFormat()===self::PATH_FORMAT)
		{
			$url=rtrim($this->getBaseUrl().'/'.$route,'/');
			foreach($params as $key=>$value)
			{
				if(is_array($value))
				{
					foreach($value as $k=>$v)
						$url.='/'.urlencode($key).'['.urlencode($k).']/'.urlencode($v);
				}
				else
					$url.='/'.urlencode($key).'/'.urlencode($value);
			}
			return $url.$this->urlSuffix;
		}
		else
		{
			$pairs=$route!==''?array($this->routeVar.'='.$route):array();
			foreach($params as $key=>$value)
			{
				if(is_array($value))
				{
					foreach($value as $k=>$v)
						$pairs[]=urlencode($key).'['.urlencode($k).']='.urlencode($v);
				}
				else
					$pairs[]=urlencode($key).'='.urlencode($value);
			}

			$baseUrl=$this->getBaseUrl();
			if(!$this->showScriptName)
				$baseUrl.='/';

			if(($query=implode($ampersand,$pairs))!=='')
				return $baseUrl.'?'.$query;
			else
				return $baseUrl;
		}
	}

	/**
	 * Parses the user request.
	 * @param CHttpRequest the request application component
	 * @return string the route that consists of the controller ID and action ID
	 */
	public function parseUrl($request)
	{
		if($this->getUrlFormat()===self::PATH_FORMAT)
		{
			$pathInfo=$this->removeUrlSuffix($request->getPathInfo());
			foreach($this->_groups as $rules)
			{
				foreach($rules as $rule)
				{
					if(($r=$rule->parseUrl($pathInfo))!==false)
					{
						$route=isset($_GET[$this->routeVar])?$_GET[$this->routeVar]:$r;
						return $this->caseSensitive?$route:strtolower($route);
					}
				}
			}
			$route=$this->parseUrlDefault($pathInfo);
		}
		else if(isset($_GET[$this->routeVar]))
			$route=$_GET[$this->routeVar];
		else if(isset($_POST[$this->routeVar]))
			$route=$_POST[$this->routeVar];
		else
			return '';

		return $this->caseSensitive?$route:strtolower($route);
	}

	/**
	 * Removes the URL suffix from path info.
	 * @param string path info part in the URL
	 * @return string path info with URL suffix removed.
	 */
	protected function removeUrlSuffix($pathInfo)
	{
		if(($ext=$this->urlSuffix)!=='' && substr($pathInfo,-strlen($ext))===$ext)
			return substr($pathInfo,0,-strlen($ext));
		else
			return $pathInfo;
	}

	/**
	 * Parses the URL using the default implementation.
	 * This method is called only when the URL format is 'get'
	 * and no appropriate rules can recognize the URL.
	 * It assumes the path info of the URL is of the following format:
	 * <pre>
	 * ControllerID/ActionID/Name1/Value1/Name2/Value2...
	 * </pre>
	 * @param string path info part of the request URL
	 * @return string the route that consists of the controller ID and action ID
	 */
	protected function parseUrlDefault($pathInfo)
	{
		$segs=explode('/',$pathInfo.'/');
		$n=count($segs);
		for($i=2;$i<$n-1;$i+=2)
		{
			$key=urldecode($segs[$i]);
			$value=urldecode($segs[$i+1]);
			if(($pos=strpos($key,'[]'))!==false)
				$_GET[substr($key,0,$pos)][]=$value;
			else
				$_GET[$key]=$value;
		}
		return $segs[0].'/'.$segs[1];
	}

	/**
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
	 * @return string the URL format. Defaults to 'path'.
	 */
	public function getUrlFormat()
	{
		return $this->_urlFormat;
	}

	/**
	 * @param string the URL format. It must be either 'path' or 'get'.
	 */
	public function setUrlFormat($value)
	{
		if($value===self::PATH_FORMAT || $value===self::GET_FORMAT)
			$this->_urlFormat=$value;
		else
			throw new CException(Yii::t('yii','CUrlManager.UrlFormat must be either "path" or "get".'));
	}
}


/**
 * CUrlRule represents a URL formatting/parsing rule.
 *
 * It mainly consists of two parts: route and pattern. The former classifies
 * the rule so that it only applies to specific controller-action route.
 * The latter performs the actual formatting and parsing role. The pattern
 * may have a set of named parameters each of specific format.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.web
 * @since 1.0
 */
class CUrlRule extends CComponent
{
	/**
	 * @var string the controller/action pair
	 */
	public $route;
	/**
	 * @var string regular expression used to parse a URL
	 */
	public $pattern;
	/**
	 * @var string template used to construct a URL
	 */
	public $template;
	/**
	 * @var array list of parameters (name=>regular expression)
	 */
	public $params;
	/**
	 * @var boolean whether the URL allows additional parameters at the end of the path info.
	 */
	public $append;
	/**
	 * @var string a token identifies the rule to a certain degree
	 */
	public $signature;
	/**
	 * @var boolean whether the rule is case sensitive. Defaults to true.
	 * @since 1.0.1
	 */
	public $caseSensitive=true;

	/**
	 * Constructor.
	 * @param string the route of the URL (controller/action)
	 * @param string the pattern for matching the URL
	 */
	public function __construct($route,$pattern)
	{
		$this->route=$route;
		if(preg_match_all('/<(\w+):?(.*?)?>/',$pattern,$matches))
			$this->params=array_combine($matches[1],$matches[2]);
		else
			$this->params=array();
		$p=rtrim($pattern,'*');
		$this->append=$p!==$pattern;
		$p=trim($p,'/');
		$this->template=preg_replace('/<(\w+):?.*?>/','<$1>',$p);
		if(($pos=strpos($p,'<'))!==false)
			$this->signature=substr($p,0,$pos);
		else
			$this->signature=$p;

		$tr['/']='\\/';
		foreach($this->params as $key=>$value)
			$tr["<$key>"]="(?P<$key>".($value!==''?$value:'[^\/]+').')';
		$this->pattern='/^'.strtr($this->template,$tr).'\/';
		if($this->append)
			$this->pattern.='/u';
		else
			$this->pattern.='$/u';
		if(!$this->caseSensitive)
			$this->pattern.='i';
		if(@preg_match($this->pattern,'test')===false)
			throw new CException(Yii::t('yii','The URL pattern "{pattern}" for route "{route}" is not a valid regular expression.',
				array('{route}'=>$route,'{pattern}'=>$pattern)));
	}

	/**
	 * @param array list of parameters
	 * @param string URL suffix
	 * @param string the token separating name-value pairs in the URL.
	 * @return string the constructed URL
	 */
	public function createUrl($params,$suffix,$ampersand)
	{
		foreach($this->params as $key=>$value)
		{
			if(!isset($params[$key]))
				return false;
		}
		$tr=array();
		$rest=array();
		$sep=$this->append?'/':'=';
		foreach($params as $key=>$value)
		{
			if(isset($this->params[$key]))
				$tr["<$key>"]=$value;
			else
			{
				if(is_array($value))
				{
					foreach($value as $k=>$v)
						$rest[]=urlencode($key).'['.urlencode($k).']'.$sep.urlencode($v);
				}
				else
					$rest[]=urlencode($key).$sep.urlencode($value);
			}
		}
		$url=strtr($this->template,$tr);
		if($rest===array())
			return $url!=='' ? $url.$suffix : $url;
		else
		{
			if($this->append)
			{
				$url.='/'.implode('/',$rest);
				if($url!=='')
					$url.=$suffix;
			}
			else
			{
				if($url!=='')
					$url.=$suffix;
				$url.='?'.implode($ampersand,$rest);
			}
			return $url;
		}
	}

	/**
	 * @param string path info part of the URL
	 * @return string the route that consists of the controller ID and action ID
	 */
	public function parseUrl($pathInfo)
	{
		$func=$this->caseSensitive?'strncmp':'strncasecmp';
		if($func($pathInfo,$this->signature,strlen($this->signature)))
			return false;

		$pathInfo.='/';
		if(preg_match($this->pattern,$pathInfo,$matches))
		{
			foreach($matches as $key=>$value)
			{
				if(is_string($key))
					$_GET[$key]=urldecode($value);
			}
			if($pathInfo!==$matches[0])
			{
				$segs=explode('/',ltrim(substr($pathInfo,strlen($matches[0])),'/'));
				$n=count($segs);
				for($i=0;$i<$n-1;$i+=2)
				{
					$key=urldecode($segs[$i]);
					$value=urldecode($segs[$i+1]);
					if(($pos=strpos($key,'[]'))!==false)
						$_GET[substr($key,0,$pos)][]=$value;
					else
						$_GET[$key]=$value;
				}
			}
			return $this->route;
		}
		else
			return false;
	}
}
