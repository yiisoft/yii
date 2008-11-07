<?php
/**
 * CClientScript class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CClientScript manages JavaScript and CSS stylesheets for views.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.web
 * @since 1.0
 */
class CClientScript extends CComponent
{
	/**
	 * @var boolean whether JavaScript should be enabled. Defaults to true.
	 */
	public $enableJavaScript=true;

	private $_controller;
	private $_packages;
	private $_dependencies;
	private $_baseUrl;
	private $_coreScripts=array();
	private $_cssFiles=array();
	private $_css=array();
	private $_scriptFiles=array();
	private $_scripts=array();
	private $_bodyScriptFiles=array();
	private $_bodyScripts=array();


	/**
	 * Constructor.
	 * @param CController controller
	 */
	public function __construct($controller)
	{
		$this->_controller=$controller;
	}

	/**
	 * Cleans all registered scripts.
	 */
	public function reset()
	{
		$this->_coreScripts=array();
		$this->_cssFiles=array();
		$this->_css=array();
		$this->_scriptFiles=array();
		$this->_scripts=array();
		$this->_bodyScriptFiles=array();
		$this->_bodyScripts=array();

		$this->_controller->recordCachingAction('clientScript','reset',array());
	}

	/**
	 * Renders the registered scripts.
	 * This method is called in {@link CController::render} when it finishes
	 * rendering content. CClientScript thus gets a chance to insert script tags
	 * at <code>head</code> and <code>body</code> sections in the HTML output.
	 * @param string the existing output that needs to be inserted with script tags
	 * @return string the modified output
	 */
	public function render($output)
	{
		$html='';
		$html2='';
		foreach($this->_cssFiles as $url=>$media)
			$html.=CHtml::cssFile($url,$media)."\n";
		foreach($this->_css as $css)
			$html.=CHtml::cssFile($css[0],$css[1])."\n";
		if($this->enableJavaScript)
		{
			foreach($this->_coreScripts as $name)
			{
				if(is_string($name))
					$html.=$this->renderCoreScript($name);
			}

			foreach($this->_scriptFiles as $scriptFile)
				$html.=CHtml::scriptFile($scriptFile)."\n";

			if(!empty($this->_scripts))
				$html.=CHtml::script(implode("\n",$this->_scripts))."\n";

			foreach($this->_bodyScriptFiles as $scriptFile)
				$html2.=CHtml::scriptFile($scriptFile)."\n";
			if(!empty($this->_bodyScripts))
				$html2.=CHtml::script(implode("\n",$this->_bodyScripts))."\n";
		}

		if($html!=='')
		{
			$output=preg_replace('/(<head\s*>.*?)(<\\/head\s*>)/is','$1'.$html.'$2',$output,1,$count);
			if(!$count)
				$output=$html.$output;
		}

		if($html2!=='')
		{
			$output=preg_replace('/(<\\/body\s*>.*?<\/html\s*>)/is',$html2.'$1',$output,1,$count);
			if(!$count)
				$output.=$html2;
		}

		return $output;
	}

	/**
	 * @return string the base URL of all core javascript files
	 */
	public function getCoreScriptUrl()
	{
		if($this->_baseUrl!==null)
			return $this->_baseUrl;
		else
			return $this->_baseUrl=Yii::app()->getAssetManager()->publish(YII_PATH.'/web/js/source');
	}

	/**
	 * Renders the specified core javascript library.
	 * Any dependent libraries will also be rendered.
	 * @param string name of core javascript library. See framework/web/js/packages.php
	 * for valid names.
	 * @return string the rendering result
	 */
	public function renderCoreScript($name)
	{
		if(isset($this->_coreScripts[$name]) && $this->_coreScripts[$name]===true || !$this->enableJavaScript)
			return '';

		$this->_coreScripts[$name]=true;
		if($this->_packages===null)
		{
			$config=require(YII_PATH.'/web/js/packages.php');
			$this->_packages=$config[0];
			$this->_dependencies=$config[1];
		}
		$baseUrl=$this->getCoreScriptUrl();
		$html='';
		if(isset($this->_dependencies[$name]))
		{
			foreach($this->_dependencies[$name] as $depName)
				$html.=$this->renderCoreScript($depName);
		}
		if(isset($this->_packages[$name]))
		{
			foreach($this->_packages[$name] as $path)
			{
				if(substr($path,-4)==='.css')
					$html.=CHtml::cssFile($baseUrl.'/'.$path)."\n";
				else
					$html.=CHtml::scriptFile($baseUrl.'/'.$path)."\n";
			}
		}
		return $html;
	}

	/**
	 * Registers a core javascript library.
	 * @param string the core javascript library name
	 * @see renderCoreScript
	 */
	public function registerCoreScript($name)
	{
		if(!isset($this->_coreScripts[$name]))
		{
			$this->_coreScripts[$name]=$name;
			$params=func_get_args();
			$this->_controller->recordCachingAction('clientScript','registerCoreScript',$params);
		}
	}

	/**
	 * Registers a CSS file
	 * @param string ID that uniquely identifies this CSS file
	 * @param string URL of the CSS file
	 * @param string media that the CSS file should be applied to. If empty, it means all media types.
	 */
	public function registerCssFile($url,$media='')
	{
		$this->_cssFiles[$url]=$media;
		$params=func_get_args();
		$this->_controller->recordCachingAction('clientScript','registerCssFile',$params);
	}

	/**
	 * Registers a piece of CSS code.
	 * @param string ID that uniquely identifies this piece of CSS code
	 * @param string the CSS code
	 * @param string media that the CSS code should be applied to. If empty, it means all media types.
	 */
	public function registerCss($id,$css,$media='')
	{
		$this->_css[$id]=array($css,$media);

		$params=func_get_args();
		$this->_controller->recordCachingAction('clientScript','registerCss',$params);
	}

	/**
	 * Registers a javascript file that should be inserted in the head section
	 * @param string URL of the javascript file
	 */
	public function registerScriptFile($url)
	{
		if(!isset($this->_scriptFiles[$url]))
		{
			$this->_scriptFiles[$url]=$url;
			$params=func_get_args();
			$this->_controller->recordCachingAction('clientScript','registerScriptFile',$params);
		}
	}

	/**
	 * Registers a piece of javascript code that should be inserted in the head section
	 * @param string ID that uniquely identifies this piece of JavaScript code
	 * @param string the javascript code
	 */
	public function registerScript($id,$script)
	{
		$this->_scripts[$id]=$script;
		$params=func_get_args();
		$this->_controller->recordCachingAction('clientScript','registerScript',$params);
	}

	/**
	 * Registers a javascript file that should be inserted in the body section
	 * @param string URL of the javascript file
	 */
	public function registerBodyScriptFile($url)
	{
		if(!isset($this->_bodyScriptFiles[$url]))
		{
			$this->_bodyScriptFiles[$url]=$url;
			$params=func_get_args();
			$this->_controller->recordCachingAction('clientScript','registerBodyScriptFile',$params);
		}
	}

	/**
	 * Registers a piece of javascript code that should be inserted in the body section
	 * @param string ID that uniquely identifies this piece of JavaScript code
	 * @param string the javascript code
	 */
	public function registerBodyScript($id,$script)
	{
		$this->_bodyScripts[$id]=$script;
		$params=func_get_args();
		$this->_controller->recordCachingAction('clientScript','registerBodyScript',$params);
	}

	/**
	 * Checks whether the CSS file has been registered.
	 * @param string URL of the CSS file
	 * @return boolean whether the CSS file is already registered
	 */
	public function isCssFileRegistered($url)
	{
		return isset($this->_cssFiles[$url]);
	}

	/**
	 * Checks whether the CSS code has been registered.
	 * @param string ID that uniquely identifies the CSS code
	 * @return boolean whether the CSS code is already registered
	 */
	public function isCssRegistered($id)
	{
		return isset($this->_css[$id]);
	}

	/**
	 * Checks whether the CSS code has been registered in the head section.
	 * @param string URL of the javascript file
	 * @return boolean whether the javascript file is already registered
	 */
	public function isScriptFileRegistered($url)
	{
		return isset($this->_scriptFiles[$url]);
	}

	/**
	 * Checks whether the JavaScript code has been registered in the head section.
	 * @param string ID that uniquely identifies the JavaScript code
	 * @return boolean whether the javascript code is already registered
	 */
	public function isScriptRegistered($id)
	{
		return isset($this->_scripts[$id]);
	}

	/**
	 * Checks whether the JavaScript file has been registered in the body section.
	 * @param string URL of the javascript file
	 * @return boolean whether the javascript file is already registered
	 */
	public function isBodyScriptFileRegistered($url)
	{
		return isset($this->_bodyScriptFiles[$url]);
	}

	/**
	 * Checks whether the JavaScript code has been registered in the body section.
	 * @param string ID that uniquely identifies the JavaScript code
	 * @return boolean whether the javascript code is already registered
	 */
	public function isBodyScriptRegistered($id)
	{
		return isset($this->_bodyScripts[$id]);
	}
}
