<?php
/**
 * This file contains classes implementing configuration feature.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */


/**
 * CConfiguration represents an array-based configuration.
 *
 * It can be used to initialize an object's properties.
 *
 * The configuration data may be obtained from a PHP script. For example,
 * <pre>
 * &lt;?php
 * return array
 * (
 *     'name'=>'My Application',
 *     'defaultController'=>'index',
 * );
 * ?&gt;
 * </pre>
 * Use the following code to load the above configuration data:
 * <pre>
 * $config=new CConfiguration('path/to/config.php');
 * </pre>
 *
 * To apply the configuration to an object, call {@link applyTo()}.
 * Each (key,value) pair in the configuration data is applied
 * to the object like: $object->$key=$value.
 *
 * Since CConfiguration extends from {@link CMap}, it can be
 * used like an associative array. See {@link CMap} for more details.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.collections
 * @since 1.0
 */
class CConfiguration extends CMap
{
	/**
	 * Constructor.
	 * @param mixed if string, it represents a config file (a PHP script returning the configuration as an array);
	 * If array, it is config data.
	 */
	public function __construct($data=null)
	{
		if(is_string($data))
			parent::__construct(require($data));
		else
			parent::__construct($data);
	}

	/**
	 * Loads configuration data from a file and merges it with the existing configuration.
	 *
	 * A config file must be a PHP script returning a configuration array (like the following)
	 * <pre>
	 * return array
	 * (
	 *     'name'=>'My Application',
	 *     'defaultController'=>'index',
	 * );
	 * </pre>
	 *
	 * @param string configuration file path (if using relative path, be aware of what is the current path)
	 * @see mergeWith
	 */
	public function loadFromFile($configFile)
	{
		$data=require($configFile);
		if($this->getCount()>0)
			$this->mergeWith($data);
		else
			$this->copyFrom($data);
	}

	/**
	 * Saves the configuration into a string.
	 * The string is a valid PHP expression representing the configuration data as an array.
	 * @return string the string representation of the configuration
	 */
	public function saveAsString()
	{
		return str_replace("\r",'',var_export($this->toArray(),true));
	}

	/**
	 * Applies the configuration to an object.
	 * Each (key,value) pair in the configuration data is applied
	 * to the object like: $object->$key=$value.
	 * @param object object to be applied with this configuration
	 */
	public function applyTo($object)
	{
		foreach($this->toArray() as $key=>$value)
			$object->$key=$value;
	}

	/**
	 * Creates an object and initializes it based on the given configuration.
	 *
	 * The specified configuration can be either a string or an array.
	 * If the former, the string is treated as the class name or
	 * {@link YiiBase::getPathOfAlias class path alias} of the object to be created.
	 * If the latter, the array must contain a 'class' element which specifies
	 * the object's class name or {@link YiiBase::getPathOfAlias class path alias}.
	 * The rest name-value pairs in the array are used to initialize
	 * the corresponding object properties.
	 *
	 * Any additional parameters passed to this method will be
	 * passed to the constructor of the object being created.
	 *
	 * NOTE: this method has been deprecated since version 1.0.1.
	 * Please use {@link YiiBase::createComponent Yii::createComponent}, instead.
	 *
	 * @param mixed the configuration. It can be either a string or an array.
	 * @return mixed the created object
	 * @throws CException if the configuration does not have 'class' value
	 */
	public static function createObject($config)
	{
		if(is_string($config))
			$config=array('class'=>$config);
		else if($config instanceof self)
			$config=$config->toArray();
		if(is_array($config) && isset($config['class']))
		{
			$className=Yii::import($config['class'],true);
			unset($config['class']);
			if(($n=func_num_args())>1)
			{
				$args=func_get_args();
				for($s='$args[1]',$i=2;$i<$n;++$i)
					$s.=",\$args[$i]";
				eval("\$object=new $className($s);");
			}
			else
				$object=new $className;
			foreach($config as $key=>$value)
				$object->$key=$value;
			return $object;
		}
		else
			throw new CException(Yii::t('yii','Object configuration must be an array containing a "class" element.'));
	}
}
