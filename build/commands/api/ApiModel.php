<?php
/**
 * ApiModel class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 * @version $Id$
 */

/**
 * ApiModel represents the documentation for the Yii framework.
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.build
 * @since 1.0
 */
class ApiModel
{
	public $classes=array();
	public $packages;

	private $_currentClass;

	public function build($sourceFiles)
	{
		$this->findClasses($sourceFiles);
		$this->processClasses();
	}

	protected function findClasses($sourceFiles)
	{
		$this->classes=array();

		foreach($sourceFiles as $file)
			require_once($file);

		$classes=array_merge(get_declared_classes(),get_declared_interfaces());
		foreach($classes as $class)
		{
			$r=new ReflectionClass($class);
			if(in_array($r->getFileName(),$sourceFiles))
				$this->classes[$class]=true;
		}
		ksort($this->classes);
	}

	protected function processClasses()
	{
		$this->packages=array();
		foreach($this->classes as $class=>$value)
		{
			$doc=$this->processClass(new ReflectionClass($class));
			$this->classes[$class]=$doc;
			$this->packages[$doc->package][]=$class;
		}
		ksort($this->packages);

		// find out child classes for each class or interface
		foreach($this->classes as $class)
		{
			if(isset($class->parentClasses[0]))
			{
				$parent=$class->parentClasses[0];
				if(isset($this->classes[$parent]))
					$this->classes[$parent]->subclasses[]=$class->name;
			}
			foreach($class->interfaces as $interface)
			{
				if(isset($this->classes[$interface]))
					$this->classes[$interface]->subclasses[]=$class->name;
			}
		}
	}

	protected function processClass($class)
	{
		$doc=new ClassDoc;
		$doc->name=$class->getName();
		$doc->loadSource($class);
		$this->_currentClass=$doc->name;
		for($parent=$class;$parent=$parent->getParentClass();)
			$doc->parentClasses[]=$parent->getName();
		foreach($class->getInterfaces() as $interface)
			$doc->interfaces[]=$interface->getName();
		$doc->isInterface=$class->isInterface();
		$doc->isAbstract=$class->isAbstract();
		$doc->isFinal=$class->isFinal();
		$doc->methods=$this->processMethods($class);
		$doc->properties=$this->processProperties($class);
		$doc->signature=($doc->isInterface?'interface ':'class ').$doc->name;
		if($doc->isFinal)
			$doc->signature='final '.$doc->signature;
		if($doc->isAbstract && !$doc->isInterface)
			$doc->signature='abstract '.$doc->signature;
		if(in_array('CComponent',$doc->parentClasses))
		{
			$doc->properties=array_merge($doc->properties,$this->processComponentProperties($class));
			$doc->events=$this->processComponentEvents($class);
		}
		ksort($doc->properties);

		foreach($doc->properties as $property)
		{
			if($property->isProtected)
				$doc->protectedPropertyCount++;
			else
				$doc->publicPropertyCount++;
			if(!$property->isInherited)
				$doc->nativePropertyCount++;
		}
		foreach($doc->methods as $method)
		{
			if($method->isProtected)
				$doc->protectedMethodCount++;
			else
				$doc->publicMethodCount++;
			if(!$method->isInherited)
				$doc->nativeMethodCount++;
		}
		foreach($doc->events as $event)
		{
			if(!$event->isInherited)
				$doc->nativeEventCount++;
		}
		$this->processComment($doc,$class->getDocComment());

		return $doc;
	}

	protected function processComment($doc,$comment)
	{
		$comment=strtr(trim(preg_replace('/^\s*\**( |\t)?/m','',trim($comment,'/'))),"\r",'');
		if(preg_match('/^\s*@\w+/m',$comment,$matches,PREG_OFFSET_CAPTURE))
		{
			$meta=substr($comment,$matches[0][1]);
			$comment=trim(substr($comment,0,$matches[0][1]));
		}
		else
			$meta='';
		if(($pos=strpos($comment,"\n"))!==false)
			$doc->introduction=$this->processDescription(substr($comment,0,$pos));
		else
			$doc->introduction=$this->processDescription($comment);

		$doc->description=$this->processDescription($comment);

		$this->processTags($doc,$meta);
	}

	protected function processDescription($text)
	{
		if(($text=trim($text))==='')
			return '';
		$text=preg_replace_callback('/\{@include\s+([^\s\}]+)\s*\}/s',array($this,'processInclude'),$text);
		$text=preg_replace('/^(\r| |\t)*$/m',"<br/><br/>",$text);
		$text=preg_replace_callback('/<pre>(.*?)<\/pre>/is',array($this,'processCode'),$text);
		$text=preg_replace_callback('/\{@link\s+([^\s\}]+)(.*?)\}/s',array($this,'processLink'),$text);
		return $text;
	}

	protected function processCode($matches)
	{
		$match=preg_replace('/<br\/><br\/>/','',$matches[1]);
		return "<pre>".htmlspecialchars($match)."</pre>";
	}

	protected function resolveInternalUrl($url)
	{
		$url=rtrim($url,'()');
		if(($pos=strpos($url,'::'))!==false)
		{
			$class=substr($url,0,$pos);
			$method=substr($url,$pos+2);
		}
		else if(isset($this->classes[$url]))
			return $url;
		else
		{
			$class=$this->_currentClass;
			$method=$url;
		}
		return $this->getMethodUrl($class,$method);
	}

	protected function getMethodUrl($class,$method)
	{
		if(!isset($this->classes[$class]))
			return '';
		if(method_exists($class,$method) || property_exists($class,$method))
			return $class.'::'.$method;
		if(method_exists($class,'get'.$method) || method_exists($class,'set'.$method))
			return $class.'::'.$method;
		if(($parent=get_parent_class($class))!==false)
			return $this->getMethodUrl($parent,$method);
		else
			return '';
	}

	protected function processLink($matches)
	{
		$url=$matches[1];
		if(($text=trim($matches[2]))==='')
			$text=$url;

		if(preg_match('/^(http|ftp):\/\//i',$url))  // an external URL
			return "<a href=\"$url\">$text</a>";
		$url=$this->resolveInternalUrl($url);
		return $url===''?$text:'{{'.$url.'|'.$text.'}}';
	}

	protected function processInclude($matches)
	{
		$class=new ReflectionClass($this->_currentClass);
		$fileName=dirname($class->getFileName()).DIRECTORY_SEPARATOR.$matches[1];
		if(is_file($fileName))
			return file_get_contents($fileName);
		else
			return $matches[0];
	}

	protected function processTags($object,$comment)
	{
		$tags=preg_split('/^\s*@/m',$comment,-1,PREG_SPLIT_NO_EMPTY);
		foreach($tags as $tag)
		{
			$segs=preg_split('/\s+/',trim($tag),2);
			$tagName=$segs[0];
			$param=isset($segs[1])?trim($segs[1]):'';
			$tagMethod='tag'.ucfirst($tagName);
			if(method_exists($this,$tagMethod))
				$this->$tagMethod($object,$param);
			else if(property_exists($object,$tagName))
				$object->$tagName=$param;
		}
	}

	protected function processMethods($class)
	{
		$methods=array();
		foreach($class->getMethods() as $method)
		{
			if($method->isPublic() || $method->isProtected())
			{
				$doc=$this->processMethod($class,$method);
				$methods[$doc->name]=$doc;
			}
		}
		ksort($methods);
		return $methods;
	}

	protected function processMethod($class,$method)
	{
		$doc=new MethodDoc;
		$doc->name=$method->getName();
		$doc->loadSource($method);
		$doc->definedBy=$method->getDeclaringClass()->getName();
		$doc->isAbstract=$method->isAbstract();
		$doc->isFinal=$method->isFinal();
		$doc->isProtected=$method->isProtected();
		$doc->isStatic=$method->isStatic();
		$doc->isInherited=$doc->definedBy!==$class->getName();

		$doc->input=array();
		foreach($method->getParameters() as $param)
		{
			$p=new ParamDoc;
			$p->name=$param->getName();
			$p->isOptional=$param->isOptional();
			if($param->isDefaultValueAvailable())
				$p->defaultValue=$param->getDefaultValue();
			$p->isPassedByReference=$param->isPassedByReference();
			$doc->input[]=$p;
		}
		reset($doc->input);

		$this->processComment($doc,$method->getDocComment());

		$params=array();
		foreach($doc->input as $param)
		{
			$type=empty($param->type)?'':$this->getTypeUrl($param->type).' ';
			if($param->isOptional)
				$params[]=$type.($param->isPassedByReference?'&':'').'$'.$param->name.'='.str_replace("\r",'',var_export($param->defaultValue,true));
			else
				$params[]=$type.($param->isPassedByReference?'&':'').'$'.$param->name;
		}
		$doc->signature='{{'.$class->name.'::'.$doc->name.'|<b>'.$doc->name.'</b>}}('.implode(', ',$params).')';
		if($doc->output!==null)
			$doc->signature=$this->getTypeUrl($doc->output->type).' '.$doc->signature;
		else
			$doc->signature='void '.$doc->signature;
		if(($modifier=implode(' ',Reflection::getModifierNames($method->getModifiers())))!=='')
			$doc->signature=$modifier.' '.$doc->signature;

		return $doc;
	}

	protected function getTypeUrl($type)
	{
		if(isset($this->classes[$type]) && $type!==$this->_currentClass)
			return '{{'.$type.'|'.$type.'}}';
		else
			return $type;
	}

	protected function processProperties($class)
	{
		$properties=array();
		foreach($class->getProperties() as $property)
		{
			if($property->isPublic() || $property->isProtected())
			{
				$p=$this->processProperty($class,$property);
				$properties[$p->name]=$p;
			}
		}
		return $properties;
	}

	protected function processProperty($class,$property)
	{
		$doc=new PropertyDoc;
		$doc->name=$property->getName();
		$doc->definedBy=$property->getDeclaringClass()->getName();
		$doc->readOnly=false;
		$doc->isStatic=$property->isStatic();
		$doc->isProtected=$property->isProtected();
		$doc->isInherited=$doc->definedBy!==$class->getName();

		$this->processComment($doc,$property->getDocComment());

		$doc->signature='<b>$'.$doc->name.'</b>;';
		if($doc->type!==null)
			$doc->signature=$this->getTypeUrl($doc->type) . ' ' . $doc->signature;
		if(($modifier=implode(' ',Reflection::getModifierNames($property->getModifiers())))!=='')
			$doc->signature=$modifier.' '.$doc->signature;

		return $doc;
	}

	protected function processComponentProperties($class)
	{
		$properties=array();
		foreach($class->getMethods() as $method)
		{
			if($this->isPropertyMethod($method) && ($method->isPublic() || $method->isProtected()))
			{
				$p=$this->processComponentProperty($class,$method);
				$properties[$p->name]=$p;
			}
		}
		return $properties;
	}

	protected function processComponentProperty($class,$method)
	{
		$doc=new PropertyDoc;
		$name=$method->getName();
		$doc->name=strtolower($name[3]).substr($name,4);
		$doc->isProtected=$method->isProtected();
		$doc->isStatic=false;
		$doc->readOnly=!$class->hasMethod('set'.substr($name,3));
		$doc->definedBy=$method->getDeclaringClass()->getName();
		$doc->isInherited=$doc->definedBy!==$class->getName();
		$doc->getter=$this->processMethod($class,$method);
		if(!$doc->readOnly)
			$doc->setter=$this->processMethod($class,$class->getMethod('set'.substr($name,3)));

		$this->processComment($doc,$method->getDocComment());

		return $doc;
	}

	protected function processComponentEvents($class)
	{
		$events=array();
		foreach($class->getMethods() as $method)
		{
			if($this->isEventMethod($method) && ($method->isPublic() || $method->isProtected()))
			{
				$e=$this->processComponentEvent($class,$method);
				$events[$e->name]=$e;
			}
		}
		return $events;
	}

	protected function processComponentEvent($class,$method)
	{
		$doc=new EventDoc;
		$doc->name=$method->getName();
		$doc->definedBy=$method->getDeclaringClass()->getName();
		$doc->isInherited=$doc->definedBy!==$class->getName();
		$doc->trigger=$this->processMethod($class,$method);

		$this->processComment($doc,$method->getDocComment());

		return $doc;
	}

	protected function tagParam($object,$comment)
	{
		if($object instanceof FunctionDoc)
		{
			$param=current($object->input);
			if($param!==false)
			{
				$segs=preg_split('/\s+/',$comment,2);
				$param->type=$segs[0];
				if(preg_match('/\[\s*\]/',$param->type))
					$param->type='array';
				if(isset($segs[1]))
				{
					/*
					 * remove $variablename from description
					 */
					$segs[1]=trim(preg_replace('/^\$\w+/','',$segs[1]));
					$param->description=$this->processDescription($segs[1]);
					if(empty($object->introduction))
					{
						if(substr($object->name,0,3)=='set')
							$object->introduction='Sets '.$param->description;
					}
				}
				next($object->input);
			}
		}
	}

	protected function tagReturn($object,$comment)
	{
		$segs=preg_split('/\s+/',$comment,2);
		if($object instanceof FunctionDoc)
		{
			$object->output=new ParamDoc;
			$object->output->type=$segs[0];
			if(isset($segs[1]))
			{
				$object->output->description=$this->processDescription($segs[1]);
				if(empty($object->introduction))
				{
					/*
					 * If no custom introduction, add automatically
					 * with this getters introduction displayed in public methods table is resolved
					 */
					if(substr($object->name,0,5)=='getIs')
						$object->introduction='Checks '.$object->output->description;
					elseif(substr($object->name,0,3)=='get')
						$object->introduction='Returns '.$object->output->description;
					elseif(substr($object->name,0,3)=='has')
						$object->introduction='Determines '.$object->output->description;
				}
			}
		}
		else if($object instanceof PropertyDoc)
		{
			$object->type=$segs[0];
			if(isset($segs[1]) && empty($object->description))
			{
				if(($pos=strpos($segs[1],'.'))!==false)
					$object->introduction=$this->processDescription(substr($segs[1],0,$pos+1));
				else
					$object->introduction=$this->processDescription($segs[1]);
				$object->description=$this->processDescription($segs[1]);
			}
		}
	}

	protected function tagVar($object,$comment)
	{
		if($object instanceof PropertyDoc)
		{
			$segs=preg_split('/\s+/',$comment,2);
			$object->type=$segs[0];
			if(isset($segs[1]) && empty($object->description))
			{
				if(($pos=strpos($segs[1],'.'))!==false)
					$object->introduction=$this->processDescription(substr($segs[1],0,$pos+1));
				else
					$object->introduction=$this->processDescription($segs[1]);
				$object->description=$this->processDescription($segs[1]);
			}
		}
	}

	protected function tagSee($object,$comment)
	{
		$segs=preg_split('/\s+/',trim($comment),2);
		$matches[1]=$segs[0];
		$matches[2]=isset($segs[1])?$segs[1]:'';
		$object->see[]=$this->processLink($matches);
	}

	protected function isPropertyMethod($method)
	{
		$methodName=$method->getName();
		return $method->getNumberOfRequiredParameters()===0
				&& !$method->isStatic()
				&& strncasecmp($methodName,'get',3)===0
				&& isset($methodName[3]);
	}

	protected function isEventMethod($method)
	{
		$methodName=$method->getName();
		return strncasecmp($methodName,'on',2)===0
				&& !$method->isStatic()
				&& isset($methodName[2]);
	}

	protected function getClassFiles($basePath)
	{
		$files=array();
		$folder=opendir($basePath);
		while($file=readdir($folder))
		{
			if($file==='.' || $file==='..')
				continue;
			$fullPath=realpath($basePath.DIRECTORY_SEPARATOR.$file);
			if($this->isValidPath($fullPath))
			{
				if(is_file($fullPath))
					$files[]=$fullPath;
				else
					$files=array_merge($files,$this->getClassFiles($fullPath));
			}
		}
		closedir($folder);
		return $files;
	}

	protected function isValidPath($path)
	{
		if(is_file($path) && substr($path,-4)!=='.php')
			return false;
		$path=strtr($path,'\\','/');
		foreach($this->_excludes as $exclude)
		{
			if(($exclude[0]==='/' && $this->_sourcePath.$exclude===$path) || ($exclude[0]!=='/' && basename($path)===$exclude))
				return false;
		}
		return true;
	}

	protected function findTargets()
	{
		$oldClasses=get_declared_classes();
		$oldInterfaces=get_declared_interfaces();
		$oldFunctions=get_defined_functions();
		$oldConstants=get_defined_constants(true);

		$classFiles=$this->getClassFiles($this->_sourcePath);
		require_once($this->_sourcePath.'/yii.php');
		foreach($classFiles as $classFile)
			require_once($classFile);

		$classes=array_values(array_diff(get_declared_classes(),$oldClasses));
		$interfaces=array_values(array_diff(get_declared_interfaces(),$oldInterfaces));
		$classes=array_merge($classes,$interfaces);

		$n=count($classes);
		for($i=0;$i<$n;++$i)
		{
			$class=new ReflectionClass($classes[$i]);
			$fileName=strtr($class->getFileName(),'\\','/');
			foreach($this->_excludes as $exclude)
			{
				if(($exclude[0]==='/' && strpos($fileName,$this->_sourcePath.$exclude)===0))
				{
					unset($classes[$i]);
					break;
				}
			}
		}

		sort($classes);
		$newFunctions=get_defined_functions();
		$newConstants=get_defined_constants(true);
		$functions=array_values(array_diff($newFunctions['user'],$oldFunctions['user']));
		$constants=$newConstants['user'];

		return array($classes,$functions,$constants);
	}

	/*
	 * Calls checkSource for every file in $sourceFiles
	 * @param array $sourceFiles array of source file path that we need to check
	 */
	public function check($sourceFiles)
	{
		echo "Checking PHPDoc @param in source files ...\n";
		foreach($sourceFiles as $no=>$sourceFile)
		{
			$this->checkSource($sourceFile);
		}
		echo "Done.\n\n";
	}

	/*
	 * Checks @param directives in a source file
	 * Detects:
	 *    missing @param directive (there is no @param directive for a function parameter)
	 *    missing function parameter (@param directive exists but that parameter is not in a function declaration)
	 *    missmatch parameters (if @param directive has different parameter name than a function - possible spelling error or wrong order of @param directives)
	 */
	protected function checkSource($sourceFile)
	{
		$fileContent=file($sourceFile);

		$docParam=array();
		foreach($fileContent as $no=>$line)
		{
			/*
			 * Get lines with @param, and parameter name
			 */
			if(preg_match('/^\s*\*\s*@param\s[A-Za-z0-9_\|]+\s(\$\w+)\s./',$line,$matches,PREG_OFFSET_CAPTURE))
			{
				$docParam[]=array(
					'docLine'=>$no+1,
					'docName'=>$matches[1][0],
				);
				continue;
			}
			/*
			 * If function without parameters, there should be no parameters in $docParam
			 */
			if(preg_match('/^\s*\w+[\s\w]*\sfunction\s\w+\(\s*\)/',$line,$matches,PREG_OFFSET_CAPTURE))
			{
				if(isset($docParam[0])) {
					$value=$docParam[0];
					echo "ERROR.............: Parameter name not found!\n";
					echo "Source file.......: ".$sourceFile."\n";
					echo "PHPDoc line.......: ".$value['docLine']."\n";
					echo "PHPDoc parameter..: ".$value['docName']."\n\n";
					$docParam=array();
				}
				continue;
			}
			/*
			 * Get function variables in $matches[1][0]
			 */
			if(preg_match('/^\s*\w+[\s\w]*\sfunction\s\w+\((.+)\)/',$line,$matches,PREG_OFFSET_CAPTURE))
			{
				$params=explode(",",$matches[1][0]);
				foreach($params as $br=>$param)
				{
					/*
					 * Strip anything that does not begin with $ (class types) eg. CHttpRequest $request
					 */
					$param=preg_replace('/^\w+/','',trim($param));
					/*
					 * Strip default value if exists ex. data=array() (with spaces)
					 */
					$param=preg_replace('/\s*=.+/','',trim($param));
					/*
					 * Strip & if pass by reference
					 */
					if($param[0]=='&')
						$param=substr($param,1);
					/*
					 * add parameter info to the docParam array
					 */
					$docParam[$br]['parameterName']=$param;
					$docParam[$br]['parameterLine']=$no+1;
				}

				/*
				 * All info gathered, let's make some checking
				 */
				foreach($docParam as $value)
				{
					if(!isset($value['docLine']) || !isset($value['docName']) && isset($value['parameterName']))
					{
						echo "ERROR.............: Documentation not found!\n";
						echo "Source file.......: ".$sourceFile."\n";
						echo "Parameter line....: ".$value['parameterLine']."\n";
						echo "Parameter name....: ".$value['parameterName']."\n\n";
					}
					if(!isset($value['parameterName']) || !isset($value['parameterLine']))
					{
						echo "ERROR.............: Parameter name not found!\n";
						echo "Source file.......: ".$sourceFile."\n";
						echo "PHPDoc line.......: ".$value['docLine']."\n";
						echo "PHPDoc parameter..: ".$value['docName']."\n\n";
					}
					if( isset($value['docName']) && isset($value['parameterName']) && $value['docName']!==$value['parameterName'])
					{
						echo "ERROR.............: Wrong parameter order!\n";
						echo "Source file.......: ".$sourceFile."\n";
						echo "PHPDoc line.......: ".$value['docLine']."\n";
						echo "PHPDoc parameter..: ".$value['docName']."\n";
						echo "Parameter line....: ".$value['parameterLine']."\n";
						echo "Parameter name....: ".$value['parameterName']."\n\n";
					}
				}
				/*
				 * reset $docParam
				 */
				$docParam=array();
			}
		}
	}

}

class BaseDoc
{
	public $name;
	public $since;
	public $see;
	public $introduction;
	public $description;

	public $sourcePath;
	public $startLine;
	public $endLine;

	public function loadSource($reflection)
	{
		$this->sourcePath=str_replace('\\','/',str_replace(YII_PATH,'',$reflection->getFileName()));
		$this->startLine=$reflection->getStartLine();
		$this->endLine=$reflection->getEndLine();
	}

	public function getSourceUrl($baseUrl,$line=null)
	{
		if($line===null)
			return $baseUrl.$this->sourcePath;
		else
			return $baseUrl.$this->sourcePath.'#'.$line;
	}

	public function getSourceCode()
	{
		$lines=file(YII_PATH.$this->sourcePath);
		return implode("",array_slice($lines,$this->startLine-1,$this->endLine-$this->startLine+1));
	}
}

class ClassDoc extends BaseDoc
{
	public $parentClasses=array();
	public $subclasses=array();
	public $interfaces=array();
	public $isInterface;
	public $isAbstract;
	public $isFinal;

	public $signature;

	public $properties=array();
	public $methods=array();
	public $events=array();
	public $constants=array();

	public $protectedPropertyCount=0;
	public $publicPropertyCount=0;
	public $protectedMethodCount=0;
	public $publicMethodCount=0;

	public $nativePropertyCount=0;
	public $nativeMethodCount=0;
	public $nativeEventCount=0;

	public $package;
	public $version;
}

class PropertyDoc extends BaseDoc
{
	public $isProtected;
	public $isStatic;
	public $readOnly;
	public $isInherited;
	public $definedBy;

	public $type;
	public $signature;

	public $getter;
	public $setter;
}

class FunctionDoc extends BaseDoc
{
	public $signature;
	public $input=array();
	public $output;
}

class MethodDoc extends FunctionDoc
{
	public $isAbstract;
	public $isFinal;
	public $isProtected;
	public $isStatic;
	public $isInherited;
	public $definedBy;
}

class EventDoc extends BaseDoc
{
	public $isInherited;
	public $definedBy;
	public $trigger;
}

class ParamDoc
{
	public $name;
	public $description;
	public $type;
	public $isOptional;
	public $defaultValue;
	public $isPassedByReference;
}
