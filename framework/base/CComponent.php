<?php
/**
 * This file contains the foundation classes for component-based and event-driven programming.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CComponent is the base class for all components.
 *
 * CComponent implements the protocol of defining, using properties and events.
 *
 * A property is defined by a getter method, and/or a setter method.
 * Properties can be accessed in the way like accessing normal object members.
 * Reading or writing a property will cause the invocation of the corresponding
 * getter or setter method, e.g.,
 * <pre>
 * $a=$component->text;     // equivalent to $a=$component->getText();
 * $component->text='abc';  // equivalent to $component->setText('abc');
 * </pre>
 * The signatures of getter and setter methods are as follows,
 * <pre>
 * // getter, defines a readable property 'text'
 * public function getText() { ... }
 * // setter, defines a writable property 'text' with $value to be set to the property
 * public function setText($value) { ... }
 * </pre>
 *
 * An event is defined by the presence of a method whose name starts with 'on'.
 * The event name is the method name. When an event is raised, functions
 * (called event handlers) attached to the event will be invoked automatically.
 *
 * An event can be raised by calling {@link raiseEvent} method, upon which
 * the attached event handlers will be invoked automatically in the order they
 * are attached to the event. Event handlers must have the following signature,
 * <pre>
 * function eventHandler($event) { ... }
 * </pre>
 * where $event includes parameters associated with the event.
 *
 * To attach an event handler to an event, see {@link attachEventHandler}.
 * You can also use the following syntax:
 * <pre>
 * $component->onClick=$callback;    // or $component->onClick->add($callback);
 * </pre>
 * where $callback refers to a valid PHP callback. Below we show some callback examples:
 * <pre>
 * 'handleOnClick'                   // handleOnClick() is a global function
 * array($object,'handleOnClick')    // using $object->handleOnClick()
 * array('Page','handleOnClick')     // using Page::handleOnClick()
 * </pre>
 *
 * To raise an event, use {@link raiseEvent}. The on-method defining an event is
 * commonly written like the following:
 * <pre>
 * public function onClick($event)
 * {
 *     $this->raiseEvent('onClick',$event);
 * }
 * </pre>
 * where <code>$event</code> is an instance of {@link CEvent} or its child class.
 * One can then raise the event by calling the on-method instead of {@link raiseEvent} directly.
 *
 * Both property names and event names are case-insensitive.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.base
 * @since 1.0
 */
class CComponent
{
	private $_e;

	/**
	 * Returns a property value or an event handler list by property or event name.
	 * Do not call this method. This is a PHP magic method that we override
	 * to allow using the following syntax to read a property or obtain event handlers:
	 * <pre>
	 * $value=$component->propertyName;
	 * $handlers=$component->eventName;
	 * </pre>
	 * @param string the property name or event name
	 * @return mixed the property value or event handlers attached to the event
	 * @throws CException if the property or event is not defined
	 * @see __set
	 */
	public function __get($name)
	{
		$getter='get'.$name;
		if(method_exists($this,$getter))
			return $this->$getter();
		else if(strncasecmp($name,'on',2)===0 && method_exists($this,$name))
		{
			// duplicating getEventHandlers() here for performance
			$name=strtolower($name);
			if(!isset($this->_e[$name]))
				$this->_e[$name]=new CList;
			return $this->_e[$name];
		}
		else
			throw new CException(Yii::t('yii','Property "{class}.{property}" is not defined.',
				array('{class}'=>get_class($this), '{property}'=>$name)));
	}

	/**
	 * Sets value of a component property.
	 * Do not call this method. This is a PHP magic method that we override
	 * to allow using the following syntax to set a property or attach an event handler
	 * <pre>
	 * $this->propertyName=$value;
	 * $this->eventName=$callback;
	 * </pre>
	 * @param string the property name or the event name
	 * @param mixed the property value or callback
	 * @throws CException if the property/event is not defined or the property is read only.
	 * @see __get
	 */
	public function __set($name,$value)
	{
		$setter='set'.$name;
		if(method_exists($this,$setter))
			$this->$setter($value);
		else if(strncasecmp($name,'on',2)===0 && method_exists($this,$name))
		{
			// duplicating getEventHandlers() here for performance
			$name=strtolower($name);
			if(!isset($this->_e[$name]))
				$this->_e[$name]=new CList;
			$this->_e[$name]->add($value);
		}
		else if(method_exists($this,'get'.$name))
			throw new CException(Yii::t('yii','Property "{class}.{property}" is read only.',
				array('{class}'=>get_class($this), '{property}'=>$name)));
		else
			throw new CException(Yii::t('yii','Property "{class}.{property}" is not defined.',
				array('{class}'=>get_class($this), '{property}'=>$name)));
	}

	/**
	 * Determines whether a property is defined.
	 * A property is defined if there is a getter or setter method
	 * defined in the class. Note, property names are case-insensitive.
	 * @param string the property name
	 * @return boolean whether the property is defined
	 * @see canGetProperty
	 * @see canSetProperty
	 */
	public function hasProperty($name)
	{
		return method_exists($this,'get'.$name) || method_exists($this,'set'.$name);
	}

	/**
	 * Determines whether a property can be read.
	 * A property can be read if the class has a getter method
	 * for the property name. Note, property name is case-insensitive.
	 * @param string the property name
	 * @return boolean whether the property can be read
	 * @see canSetProperty
	 */
	public function canGetProperty($name)
	{
		return method_exists($this,'get'.$name);
	}

	/**
	 * Determines whether a property can be set.
	 * A property can be written if the class has a setter method
	 * for the property name. Note, property name is case-insensitive.
	 * @param string the property name
	 * @return boolean whether the property can be written
	 * @see canGetProperty
	 */
	public function canSetProperty($name)
	{
		return method_exists($this,'set'.$name);
	}

	/**
	 * Determines whether an event is defined.
	 * An event is defined if the class has a method named like 'onXXX'.
	 * Note, event name is case-insensitive.
	 * @param string the event name
	 * @return boolean whether an event is defined
	 */
	public function hasEvent($name)
	{
		return !strncasecmp($name,'on',2) && method_exists($this,$name);
	}

	/**
	 * Checks whether the named event has attached handlers.
	 * @param string the event name
	 * @return boolean whether an event has been attached one or several handlers
	 */
	public function hasEventHandler($name)
	{
		$name=strtolower($name);
		return isset($this->_e[$name]) && $this->_e[$name]->getCount()>0;
	}

	/**
	 * Returns the list of attached event handlers for an event.
	 * @param string the event name
	 * @return CList list of attached event handlers for the event
	 * @throws CException if the event is not defined
	 */
	public function getEventHandlers($name)
	{
		if($this->hasEvent($name))
		{
			$name=strtolower($name);
			if(!isset($this->_e[$name]))
				$this->_e[$name]=new CList;
			return $this->_e[$name];
		}
		else
			throw new CException(Yii::t('yii','Event "{class}.{event}" is not defined.',
				array('{class}'=>get_class($this), '{event}'=>$name)));
	}

	/**
	 * Attaches an event handler to an event.
	 *
	 * An event handler must be a valid PHP callback, i.e., a string referring to
	 * a global function name, or an array containing two elements with
	 * the first element being an object and the second element a method name
	 * of the object.
	 *
	 * An event handler must be defined with the following signature,
	 * <pre>
	 * function handlerName($event) {}
	 * </pre>
	 * where $event includes parameters associated with the event.
	 *
	 * This is a convenient method of attaching a handler to an event.
	 * It is equivalent to the following code:
	 * <pre>
	 * $component->getEventHandlers($eventName)->add($eventHandler);
	 * </pre>
	 *
	 * Using {@link getEventHandlers}, one can also specify the excution order
	 * of multiple handlers attaching to the same event. For example:
	 * <pre>
	 * $component->getEventHandlers($eventName)->insertAt(0,$eventHandler);
	 * </pre>
	 * makes the handler to be invoked first.
	 *
	 * @param string the event name
	 * @param callback the event handler
	 * @throws CException if the event is not defined
	 * @see detachEventHandler
	 */
	public function attachEventHandler($name,$handler)
	{
		$this->getEventHandlers($name)->add($handler);
	}

	/**
	 * Detaches an existing event handler.
	 * This method is the opposite of {@link attachEventHandler}.
	 * @param string event name
	 * @param callback the event handler to be removed
	 * @return boolean if the detachment process is successful
	 * @see attachEventHandler
	 */
	public function detachEventHandler($name,$handler)
	{
		if($this->hasEventHandler($name))
		{
			try
			{
				$this->getEventHandlers($name)->remove($handler);
				return true;
			}
			catch(Exception $e)
			{
			}
		}
		return false;
	}

	/**
	 * Raises an event.
	 * This method represents the happening of an event. It invokes
	 * all attached handlers for the event.
	 * @param string the event name
	 * @param CEvent the event parameter
	 * @throws CException if the event is undefined or an event handler is invalid.
	 */
	public function raiseEvent($name,$event)
	{
		$name=strtolower($name);
		if(isset($this->_e[$name]))
		{
			foreach($this->_e[$name] as $handler)
			{
				if(is_string($handler))
					call_user_func($handler,$event);
				else if(is_callable($handler,true))
				{
					// an array: 0 - object, 1 - method name
					list($object,$method)=$handler;
					if(is_string($object))	// static method call
						call_user_func($handler,$event);
					else if(method_exists($object,$method))
						$object->$method($event);
					else
						throw new CException(Yii::t('yii','Event "{class}.{event}" is attached with an invalid handler "{handler}".',
							array('{class}'=>get_class($this), '{event}'=>$name, '{handler}'=>$handler[1])));
				}
				else
					throw new CException(Yii::t('yii','Event "{class}.{event}" is attached with an invalid handler "{handler}".',
						array('{class}'=>get_class($this), '{event}'=>$name, '{handler}'=>gettype($handler))));
				// stop further handling if param.handled is set true
				if(($event instanceof CEvent) && $event->handled)
					return;
			}
		}
		else if(!$this->hasEvent($name))
			throw new CException(Yii::t('yii','Event "{class}.{event}" is not defined.',
				array('{class}'=>get_class($this), '{event}'=>$name)));
	}
}


/**
 * CEvent is the base class for all event classes.
 *
 * It encapsulates the parameters associated with an event.
 * The {@link sender} property describes who raises the event.
 * And the {@link handled} property indicates if the event is handled.
 * If an event handler sets {@link handled} to true, those handlers
 * that are not invoked yet will not be invoked anymore.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.base
 * @since 1.0
 */
class CEvent extends CComponent
{
	/**
	 * @var object the sender of this event
	 */
	public $sender;
	/**
	 * @var boolean whether the event is handled. Defaults to false.
	 * When a handler sets this true, the rest uninvoked handlers will not be invoked anymore.
	 */
	public $handled=false;

	/**
	 * Constructor.
	 * @param mixed sender of the event
	 */
	public function __construct($sender=null)
	{
		$this->sender=$sender;
	}
}


/**
 * CEnumerable is the base class for all enumerable types.
 *
 * To define an enumerable type, extend CEnumberable and define string constants.
 * Each constant represents an enumerable value.
 * The constant name must be the same as the constant value.
 * For example,
 * <pre>
 * class TextAlign extends CEnumerable
 * {
 *     const Left='Left';
 *     const Right='Right';
 * }
 * </pre>
 * Then, one can use the enumerable values such as TextAlign::Left and
 * TextAlign::Right.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.base
 * @since 1.0
 */
class CEnumerable
{
}


/**
 * CPropertyValue is a helper class that provides static methods to convert component property values to specific types.
 *
 * CPropertyValue is commonly used in component setter methods to ensure
 * the new property value is of the specific type.
 * For example, a boolean-typed property setter method would be as follows,
 * <pre>
 * public function setPropertyName($value)
 * {
 *     $value=CPropertyValue::ensureBoolean($value);
 *     // $value is now of boolean type
 * }
 * </pre>
 *
 * Properties can be of the following types with specific type conversion rules:
 * <ul>
 * <li>string: a boolean value will be converted to 'true' or 'false'.</li>
 * <li>boolean: string 'true' (case-insensitive) will be converted to true,
 *            string 'false' (case-insensitive) will be converted to false.</li>
 * <li>integer</li>
 * <li>float</li>
 * <li>array: string starting with '(' and ending with ')' will be considered as
 *          as an array expression and will be evaluated. Otherwise, an array
 *          with the value to be ensured is returned.</li>
 * <li>object</li>
 * <li>enum: enumerable type, represented by an array of strings.</li>
 * </ul>
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.base
 * @since 1.0
 */
class CPropertyValue
{
	/**
	 * Converts a value to boolean type.
	 * Note, string 'true' (case-insensitive) will be converted to true,
	 * string 'false' (case-insensitive) will be converted to false.
	 * If a string represents a non-zero number, it will be treated as true.
	 * @param mixed the value to be converted.
	 * @return boolean
	 */
	public static function ensureBoolean($value)
	{
		if (is_string($value))
			return !strcasecmp($value,'true') || $value!=0;
		else
			return (boolean)$value;
	}

	/**
	 * Converts a value to string type.
	 * Note, a boolean value will be converted to 'true' if it is true
	 * and 'false' if it is false.
	 * @param mixed the value to be converted.
	 * @return string
	 */
	public static function ensureString($value)
	{
		if (is_bool($value))
			return $value?'true':'false';
		else
			return (string)$value;
	}

	/**
	 * Converts a value to integer type.
	 * @param mixed the value to be converted.
	 * @return integer
	 */
	public static function ensureInteger($value)
	{
		return (integer)$value;
	}

	/**
	 * Converts a value to float type.
	 * @param mixed the value to be converted.
	 * @return float
	 */
	public static function ensureFloat($value)
	{
		return (float)$value;
	}

	/**
	 * Converts a value to array type. If the value is a string and it is
	 * in the form (a,b,c) then an array consisting of each of the elements
	 * will be returned. If the value is a string and it is not in this form
	 * then an array consisting of just the string will be returned. If the value
	 * is not a string then
	 * @param mixed the value to be converted.
	 * @return array
	 */
	public static function ensureArray($value)
	{
		if(is_string($value))
		{
			$value = trim($value);
			$len = strlen($value);
			if ($len >= 2 && $value[0] == '(' && $value[$len-1] == ')')
			{
				eval('$array=array'.$value.';');
				return $array;
			}
			else
				return $len>0?array($value):array();
		}
		else
			return (array)$value;
	}

	/**
	 * Converts a value to object type.
	 * @param mixed the value to be converted.
	 * @return object
	 */
	public static function ensureObject($value)
	{
		return (object)$value;
	}

	/**
	 * Converts a value to enum type.
	 *
	 * This method checks if the value is of the specified enumerable type.
	 * A value is a valid enumerable value if it is equal to the name of a constant
	 * in the specified enumerable type (class).
	 * For more details about enumerable, see {@link CEnumerable}.
	 *
	 * @param string the enumerable value to be checked.
	 * @param string the enumerable class name (make sure it is included before calling this function).
	 * @return string the valid enumeration value
	 * @throws CException if the value is not a valid enumerable value
	 */
	public static function ensureEnum($value,$enumType)
	{
		static $types=array();
		if(!isset($types[$enumType]))
			$types[$enumType]=new ReflectionClass($enumType);
		if($types[$enumType]->hasConstant($value))
			return $value;
		else
			throw new CException(Yii::t('yii','Invalid enumerable value "{value}". Please make sure it is among ({enum}).',
				array('{value}'=>$value, '{enum}'=>implode(', ',$types[$enumType]->getConstants()))));
	}
}
