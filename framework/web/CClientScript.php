<?php
/**
 * CClientScript class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
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
class CClientScript extends CApplicationComponent
{
	/**
	 * The script is rendered in the head section right before the title element.
	 */
	const POS_HEAD=0;
	/**
	 * The script is rendered at the beginning of the body section.
	 */
	const POS_BEGIN=1;
	/**
	 * The script is rendered at the end of the body section.
	 */
	const POS_END=2;
	/**
	 * The script is rendered inside window onload function.
	 */
	const POS_LOAD=3;
	/**
	 * The body script is rendered inside a jQuery ready function.
	 */
	const POS_READY=4;

	/**
	 * @var boolean whether JavaScript should be enabled. Defaults to true.
	 */
	public $enableJavaScript=true;

	private $_hasScripts=false;
	private $_packages;
	private $_dependencies;
	private $_baseUrl;
	private $_coreScripts=array();
	private $_cssFiles=array();
	private $_css=array();
	private $_scriptFiles=array();
	private $_scripts=array();
	private $_metas=array();
	private $_links=array();

	/**
	 * Cleans all registered scripts.
	 */
	public function reset()
	{
		$this->_hasScripts=false;
		$this->_coreScripts=array();
		$this->_cssFiles=array();
		$this->_css=array();
		$this->_scriptFiles=array();
		$this->_scripts=array();
		$this->_metas=array();
		$this->_links=array();

		Yii::app()->getController()->recordCachingAction('clientScript','reset',array());
	}

	/**
	 * Renders the registered scripts.
	 * This method is called in {@link CController::render} when it finishes
	 * rendering content. CClientScript thus gets a chance to insert script tags
	 * at <code>head</code> and <code>body</code> sections in the HTML output.
	 * @param string the existing output that needs to be inserted with script tags
	 */
	public function render(&$output)
	{
		if(!$this->_hasScripts)
			return;

		$this->renderHead($output);
		if($this->enableJavaScript)
		{
			$this->renderBodyBegin($output);
			$this->renderBodyEnd($output);
		}
	}

	/**
	 * Inserts the scripts in the head section.
	 * @param string the output to be inserted with scripts.
	 */
	public function renderHead(&$output)
	{
		$html='';
		foreach($this->_metas as $meta)
			$html.=CHtml::metaTag($meta['content'],null,null,$meta);
		foreach($this->_links as $link)
			$html.=CHtml::linkTag(null,null,null,null,$link);
		foreach($this->_cssFiles as $url=>$media)
			$html.=CHtml::cssFile($url,$media)."\n";
		foreach($this->_css as $css)
			$html.=CHtml::css($css[0],$css[1])."\n";
		if($this->enableJavaScript)
		{
			foreach($this->_coreScripts as $name)
			{
				if(is_string($name))
					$html.=$this->renderCoreScript($name);
			}

			if(isset($this->_scriptFiles[self::POS_HEAD]))
			{
				foreach($this->_scriptFiles[self::POS_HEAD] as $scriptFile)
					$html.=CHtml::scriptFile($scriptFile)."\n";
			}

			if(isset($this->_scripts[self::POS_HEAD]))
				$html.=CHtml::script(implode("\n",$this->_scripts[self::POS_HEAD]))."\n";
		}

		if($html!=='')
		{
			$output=preg_replace('/(<title\b[^>]*>|<\\/head\s*>)/is','<###head###>$1',$output,1,$count);
			if($count)
				$output=str_replace('<###head###>',$html,$output);
			else
				$output=$html.$output;
		}
	}

	/**
	 * Inserts the scripts at the beginning of the body section.
	 * @param string the output to be inserted with scripts.
	 */
	public function renderBodyBegin(&$output)
	{
		$html='';
		if(isset($this->_scriptFiles[self::POS_BEGIN]))
		{
			foreach($this->_scriptFiles[self::POS_BEGIN] as $scriptFile)
				$html.=CHtml::scriptFile($scriptFile)."\n";
		}
		if(isset($this->_scripts[self::POS_BEGIN]))
			$html.=CHtml::script(implode("\n",$this->_scripts[self::POS_BEGIN]))."\n";

		if($html!=='')
		{
			$output=preg_replace('/(<body\b[^>]*>)/is','$1<###begin###>',$output,1,$count);
			if($count)
				$output=str_replace('<###begin###>',$html,$output);
			else
				$output=$html.$output;
		}
	}

	/**
	 * Inserts the scripts at the end of the body section.
	 * @param string the output to be inserted with scripts.
	 */
	public function renderBodyEnd(&$output)
	{
		if(!isset($this->_scriptFiles[self::POS_END]) && !isset($this->_scripts[self::POS_END])
			&& !isset($this->_scripts[self::POS_READY]) && !isset($this->_scripts[self::POS_LOAD]))
			return;

		$output=preg_replace('/(<\\/body\s*>)/is','<###end###>$1',$output,1,$fullPage);
		$html='';
		if(isset($this->_scriptFiles[self::POS_END]))
		{
			foreach($this->_scriptFiles[self::POS_END] as $scriptFile)
				$html.=CHtml::scriptFile($scriptFile)."\n";
		}
		$scripts=isset($this->_scripts[self::POS_END]) ? $this->_scripts[self::POS_END] : array();
		if(isset($this->_scripts[self::POS_READY]))
		{
			if($fullPage)
				$scripts[]="jQuery(document).ready(function() {\n".implode("\n",$this->_scripts[self::POS_READY])."\n});";
			else
				$scripts[]=implode("\n",$this->_scripts[self::POS_READY]);
		}
		if(isset($this->_scripts[self::POS_LOAD]))
		{
			if($fullPage)
				$scripts[]="window.onload=function() {\n".implode("\n",$this->_scripts[self::POS_LOAD])."\n};";
			else
				$scripts[]=implode("\n",$this->_scripts[self::POS_LOAD]);
		}
		if(!empty($scripts))
			$html.=CHtml::script(implode("\n",$scripts))."\n";

		if($fullPage)
			$output=str_replace('<###end###>',$html,$output);
		else
			$output=$output.$html;
	}

	/**
	 * Returns the base URL of all core javascript files.
	 * If the base URL is not explicitly set, this method will publish the whole directory
	 * 'framework/web/js/source' and return the corresponding URL.
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
	 * Sets the base URL of all core javascript files.
	 * This setter is provided in case when core javascript files are manually published
	 * to a pre-specified location. This may save asset publishing time for large-scale applications.
	 * @param string the base URL of all core javascript files.
	 */
	public function setCoreScriptUrl($value)
	{
		$this->_baseUrl=$value;
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
		$this->_hasScripts=true;
		$this->_coreScripts[$name]=$name;
		$params=func_get_args();
		Yii::app()->getController()->recordCachingAction('clientScript','registerCoreScript',$params);
	}

	/**
	 * Registers a CSS file
	 * @param string ID that uniquely identifies this CSS file
	 * @param string URL of the CSS file
	 * @param string media that the CSS file should be applied to. If empty, it means all media types.
	 */
	public function registerCssFile($url,$media='')
	{
		$this->_hasScripts=true;
		$this->_cssFiles[$url]=$media;
		$params=func_get_args();
		Yii::app()->getController()->recordCachingAction('clientScript','registerCssFile',$params);
	}

	/**
	 * Registers a piece of CSS code.
	 * @param string ID that uniquely identifies this piece of CSS code
	 * @param string the CSS code
	 * @param string media that the CSS code should be applied to. If empty, it means all media types.
	 */
	public function registerCss($id,$css,$media='')
	{
		$this->_hasScripts=true;
		$this->_css[$id]=array($css,$media);
		$params=func_get_args();
		Yii::app()->getController()->recordCachingAction('clientScript','registerCss',$params);
	}

	/**
	 * Registers a javascript file.
	 * @param string URL of the javascript file
	 * @param integer the position of the JavaScript code. Valid values include the following:
	 * <ul>
	 * <li>CClientScript::POS_HEAD : the script is inserted in the head section right before the title element.</li>
	 * <li>CClientScript::POS_BEGIN : the script is inserted at the beginning of the body section.</li>
	 * <li>CClientScript::POS_END : the script is inserted at the end of the body section.</li>
	 * </ul>
	 */
	public function registerScriptFile($url,$position=self::POS_HEAD)
	{
		$this->_hasScripts=true;
		$this->_scriptFiles[$position][$url]=$url;
		$params=func_get_args();
		Yii::app()->getController()->recordCachingAction('clientScript','registerScriptFile',$params);
	}

	/**
	 * Registers a piece of javascript code.
	 * @param string ID that uniquely identifies this piece of JavaScript code
	 * @param string the javascript code
	 * @param integer the position of the JavaScript code. Valid values include the following:
	 * <ul>
	 * <li>CClientScript::POS_HEAD : the script is inserted in the head section right before the title element.</li>
	 * <li>CClientScript::POS_BEGIN : the script is inserted at the beginning of the body section.</li>
	 * <li>CClientScript::POS_END : the script is inserted at the end of the body section.</li>
	 * <li>CClientScript::POS_LOAD : the script is inserted in the window.onload() function.</li>
	 * <li>CClientScript::POS_READY : the script is inserted in the jQuery's ready function.</li>
	 * </ul>
	 */
	public function registerScript($id,$script,$position=self::POS_READY)
	{
		$this->_hasScripts=true;
		$this->_scripts[$position][$id]=$script;
		if($position===self::POS_READY)
			$this->registerCoreScript('jquery');
		$params=func_get_args();
		Yii::app()->getController()->recordCachingAction('clientScript','registerScript',$params);
	}

	/**
	 * Registers a meta tag that will be inserted in the head section (right before the title element) of the resulting page.
	 * @param string content attribute of the meta tag
	 * @param string name attribute of the meta tag. If null, the attribute will not be generated
	 * @param string http-equiv attribute of the meta tag. If null, the attribute will not be generated
	 * @param array other options in name-value pairs (e.g. 'scheme', 'lang')
	 * @since 1.0.1
	 */
	public function registerMetaTag($content,$name=null,$httpEquiv=null,$options=array())
	{
		$options['content']=$content;
		if($name!==null)
			$options['name']=$name;
		if($httpEquiv!==null)
			$options['http-equiv']=$httpEquiv;
		$this->_metas[serialize($options)]=$options;
		$params=func_get_args();
		Yii::app()->getController()->recordCachingAction('clientScript','registerMetaTag',$params);
	}

	/**
	 * Registers a link tag that will be inserted in the head section (right before the title element) of the resulting page.
	 * @param string rel attribute of the link tag. If null, the attribute will not be generated.
	 * @param string type attribute of the link tag. If null, the attribute will not be generated.
	 * @param string href attribute of the link tag. If null, the attribute will not be generated.
	 * @param string media attribute of the link tag. If null, the attribute will not be generated.
	 * @param array other options in name-value pairs
	 * @since 1.0.1
	 */
	public function registerLinkTag($relation=null,$type=null,$href=null,$media=null,$options=array())
	{
		if($relation!==null)
			$options['rel']=$relation;
		if($type!==null)
			$options['type']=$type;
		if($href!==null)
			$options['href']=$href;
		if($media!==null)
			$options['media']=$media;
		$this->_links[serialize($options)]=$options;
		$params=func_get_args();
		Yii::app()->getController()->recordCachingAction('clientScript','registerLinkTag',$params);
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
	 * Checks whether the JavaScript file has been registered.
	 * @param string URL of the javascript file
	 * @param integer the position of the JavaScript code. Valid values include the following:
	 * <ul>
	 * <li>CClientScript::POS_HEAD : the script is inserted in the head section right before the title element.</li>
	 * <li>CClientScript::POS_BEGIN : the script is inserted at the beginning of the body section.</li>
	 * <li>CClientScript::POS_END : the script is inserted at the end of the body section.</li>
	 * </ul>
	 * @return boolean whether the javascript file is already registered
	 */
	public function isScriptFileRegistered($url,$position=self::POS_HEAD)
	{
		return isset($this->_scriptFiles[$position][$url]);
	}

	/**
	 * Checks whether the JavaScript code has been registered.
	 * @param string ID that uniquely identifies the JavaScript code
	 * @param integer the position of the JavaScript code. Valid values include the following:
	 * <ul>
	 * <li>CClientScript::POS_HEAD : the script is inserted in the head section right before the title element.</li>
	 * <li>CClientScript::POS_BEGIN : the script is inserted at the beginning of the body section.</li>
	 * <li>CClientScript::POS_END : the script is inserted at the end of the body section.</li>
	 * <li>CClientScript::POS_LOAD : the script is inserted in the window.onload() function.</li>
	 * <li>CClientScript::POS_READY : the script is inserted in the jQuery's ready function.</li>
	 * </ul>
	 * @return boolean whether the javascript code is already registered
	 */
	public function isScriptRegistered($id,$position=self::POS_READY)
	{
		return isset($this->_scripts[$position][$id]);
	}
}
