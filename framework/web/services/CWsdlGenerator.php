<?php
/**
 * CWsdlGenerator class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CWsdlGenerator generates the WSDL for a given service class.
 *
 * The WSDL generation is based on the doc comments found in the service class file.
 * In particular, it recognizes the '@soap' tag in the comment and extracts
 * API method and type definitions.
 *
 * In a service class, a remote invokable method must be a public method with a doc
 * comment block containing the '@soap' tag. In the doc comment, the type and name
 * of every input parameter and the type of the return value should be declared using
 * the standard phpdoc format.
 *
 * CWsdlGenerator recognizes the following primitive types (case-sensitive) in
 * the parameter and return type declarations:
 * <ul>
 * <li>str/string: maps to xsd:string;</li>
 * <li>int/integer: maps to xsd:int;</li>
 * <li>float/double: maps to xsd:float;</li>
 * <li>bool/boolean: maps to xsd:boolean;</li>
 * <li>date: maps to xsd:date;</li>
 * <li>time: maps to xsd:time;</li>
 * <li>datetime: maps to xsd:dateTime;</li>
 * <li>array: maps to xsd:string;</li>
 * <li>object: maps to xsd:struct;</li>
 * <li>mixed: maps to xsd:anyType.</li>
 * </ul>
 *
 * If a type is not a primitive type, it is considered as a class type, and
 * CWsdlGenerator will look for its property declarations. Only public properties
 * are considered, and they each must be associated with a doc comment block containg
 * the '@soap' tag. The doc comment block should declare the type of the property.
 *
 * CWsdlGenerator recognizes the array type with the following format:
 * <pre>
 * typeName[]: maps to tns:typeNameArray
 * </pre>
 *
 * The following is an example declaring a remote invokable method:
 * <pre>
 * / **
 *   * A foo method.
 *   * @param string name of something
 *   * @param string value of something
 *   * @return string[] some array
 *   * @soap
 *   * /
 * public function foo($name,$value) {...}
 * </pre>
 *
 * And the following is an example declaring a class with remote accessible properties:
 * <pre>
 * class Foo {
 *     / **
 *       * @var string name of foo
 *       * @soap
 *       * /
 *     public $name;
 *     / **
 *       * @var Member[] members of foo
 *       * @soap
 *       * /
 *     public $members;
 * }
 * </pre>
 * In the above, the 'members' property is an array of 'Member' objects. Since 'Member' is not
 * a primitive type, CWsdlGenerator will look further to find the definition of 'Member'.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.web.services
 * @since 1.0
 */
class CWsdlGenerator extends CComponent
{
	/**
	 * @var string the namespace to be used in the generated WSDL.
	 * If not set, it defaults to the name of the class that WSDL is generated upon.
	 */
	public $namespace;
	/**
	 * @var string the name of the generated WSDL.
	 * If not set, it defaults to "urn:{$className}wsdl".
	 */
	public $serviceName;

	protected static $typeMap=array(
		'string'=>'xsd:string',
		'str'=>'xsd:string',
		'int'=>'xsd:int',
		'integer'=>'xsd:integer',
		'float'=>'xsd:float',
		'double'=>'xsd:float',
		'bool'=>'xsd:boolean',
		'boolean'=>'xsd:boolean',
		'date'=>'xsd:date',
		'time'=>'xsd:time',
		'datetime'=>'xsd:dateTime',
		'array'=>'soap-enc:Array',
		'object'=>'xsd:struct',
		'mixed'=>'xsd:anyType',
	);
	
	private $_operations;
	private $_types;
	private $_messages;

	/**
	 * Generates the WSDL for the given class.
	 * @param string $className class name
	 * @param string $serviceUrl Web service URL
	 * @param string $encoding encoding of the WSDL. Defaults to 'UTF-8'.
	 * @return string the generated WSDL
	 */
	public function generateWsdl($className, $serviceUrl, $encoding='UTF-8')
	{
		$this->_operations=array();
		$this->_types=array();
		$this->_messages=array();
		if($this->serviceName===null)
			$this->serviceName=$className;
		if($this->namespace===null)
			$this->namespace="urn:{$className}wsdl";

		$reflection=new ReflectionClass($className);
		foreach($reflection->getMethods() as $method)
		{
			if($method->isPublic())
				$this->processMethod($method);
		}

		return $this->buildDOM($serviceUrl,$encoding)->saveXML();
	}

	/*
	 * @param ReflectionMethod $method method
	 */
	private function processMethod($method)
	{
		$comment=$method->getDocComment();
		if(strpos($comment,'@soap')===false)
			return;
		$comment=strtr($comment,array("\r\n"=>"\n","\r"=>"\n")); // make line endings consistent: win -> unix, mac -> unix

		$methodName=$method->getName();
		$comment=preg_replace('/^\s*\**(\s*?$|\s*)/m','',$comment);
		$params=$method->getParameters();
		$message=array();
		$n=preg_match_all('/^@param\s+([\w\.]+(\[\s*\])?)\s*?(.*)$/im',$comment,$matches);
		if($n>count($params))
			$n=count($params);
		for($i=0;$i<$n;++$i)
			$message[$params[$i]->getName()]=array($this->processType($matches[1][$i]), trim($matches[3][$i])); // name => type, doc

		$this->_messages[$methodName.'Request']=$message;

		if(preg_match('/^@return\s+([\w\.]+(\[\s*\])?)\s*?(.*)$/im',$comment,$matches))
			$return=array($this->processType($matches[1]),trim($matches[2])); // type, doc
		else
			$return=null;
		$this->_messages[$methodName.'Response']=array('return'=>$return);

		if(preg_match('/^\/\*+\s*([^@]*?)\n@/s',$comment,$matches))
			$doc=trim($matches[1]);
		else
			$doc='';
		$this->_operations[$methodName]=$doc;
	}

	/*
	 * @param string $type PHP variable type
	 */
	private function processType($type)
	{
		if(isset(self::$typeMap[$type]))
			return self::$typeMap[$type];
		else if(isset($this->_types[$type]))
			return is_array($this->_types[$type]) ? 'tns:'.$type : $this->_types[$type];
		else if(($pos=strpos($type,'[]'))!==false) // if it is an array
		{
			$type=substr($type,0,$pos);
			$this->_types[$type.'[]']='tns:'.$type.'Array';
			$this->processType($type);
			return $this->_types[$type.'[]'];
		}
		else // class type
		{
			$type=Yii::import($type,true);
			$this->_types[$type]=array();
			$class=new ReflectionClass($type);
			foreach($class->getProperties() as $property)
			{
				$comment=$property->getDocComment();
				if($property->isPublic() && strpos($comment,'@soap')!==false)
				{
					if(preg_match('/@var\s+([\w\.]+(\[\s*\])?)\s*?(.*)$/mi',$comment,$matches))
						$this->_types[$type][$property->getName()]=array($this->processType($matches[1]),trim($matches[3]));  // name => type, doc
				}
			}
			return 'tns:'.$type;
		}
	}

	/*
	 * @param string $serviceUrl Web service URL
	 * @param string $encoding encoding of the WSDL. Defaults to 'UTF-8'.
	 */
	private function buildDOM($serviceUrl,$encoding)
	{
		$xml="<?xml version=\"1.0\" encoding=\"$encoding\"?>
<definitions name=\"{$this->serviceName}\" targetNamespace=\"{$this->namespace}\"
     xmlns=\"http://schemas.xmlsoap.org/wsdl/\"
     xmlns:tns=\"{$this->namespace}\"
     xmlns:soap=\"http://schemas.xmlsoap.org/wsdl/soap/\"
     xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\"
	 xmlns:wsdl=\"http://schemas.xmlsoap.org/wsdl/\"
     xmlns:soap-enc=\"http://schemas.xmlsoap.org/soap/encoding/\"></definitions>";

		$dom=new DOMDocument();
		$dom->loadXml($xml);
		$this->addTypes($dom);

		$this->addMessages($dom);
		$this->addPortTypes($dom);
		$this->addBindings($dom);
		$this->addService($dom,$serviceUrl);

		return $dom;
	}

	/*
	 * @param DOMDocument $dom Represents an entire HTML or XML document; serves as the root of the document tree
	 */
	private function addTypes($dom)
	{
		if($this->_types===array())
			return;
		$types=$dom->createElement('wsdl:types');
		$schema=$dom->createElement('xsd:schema');
		$schema->setAttribute('targetNamespace',$this->namespace);
		foreach($this->_types as $phpType=>$xmlType)
		{
			if(is_string($xmlType) && strrpos($xmlType,'Array')!==strlen($xmlType)-5)
				continue;  // simple type
			$complexType=$dom->createElement('xsd:complexType');
			if(is_string($xmlType))
			{
				if(($pos=strpos($xmlType,'tns:'))!==false)
					$complexType->setAttribute('name',substr($xmlType,4));
				else
					$complexType->setAttribute('name',$xmlType);
				$complexContent=$dom->createElement('xsd:complexContent');
				$restriction=$dom->createElement('xsd:restriction');
				$restriction->setAttribute('base','soap-enc:Array');
				$attribute=$dom->createElement('xsd:attribute');
				$attribute->setAttribute('ref','soap-enc:arrayType');
				$attribute->setAttribute('wsdl:arrayType',substr($xmlType,0,strlen($xmlType)-5).'[]');
				
				$arrayType = ($dppos=strpos($xmlType,':')) !==false ? substr($xmlType,$dppos + 1) : $xmlType; // strip namespace, if any
				$arrayType = substr($arrayType,0,-5); // strip 'Array' from name
				$arrayType = (isset(self::$typeMap[$arrayType]) ? 'xsd:' : 'tns:') .$arrayType.'[]'; 
				$attribute->setAttribute('wsdl:arrayType',$arrayType);
				
				$restriction->appendChild($attribute);
				$complexContent->appendChild($restriction);
				$complexType->appendChild($complexContent);
			}
			else if(is_array($xmlType))
			{
				$complexType->setAttribute('name',$phpType);
				$all=$dom->createElement('xsd:all');
				foreach($xmlType as $name=>$type)
				{
					$element=$dom->createElement('xsd:element');
					$element->setAttribute('name',$name);
					$element->setAttribute('type',$type[0]);
					$all->appendChild($element);
				}
				$complexType->appendChild($all);
			}
			$schema->appendChild($complexType);
			$types->appendChild($schema);
		}

		$dom->documentElement->appendChild($types);
	}

	/*
	 * @param DOMDocument $dom Represents an entire HTML or XML document; serves as the root of the document tree
	 */
	private function addMessages($dom)
	{
		foreach($this->_messages as $name=>$message)
		{
			$element=$dom->createElement('wsdl:message');
			$element->setAttribute('name',$name);
			foreach($this->_messages[$name] as $partName=>$part)
			{
				if(is_array($part))
				{
					$partElement=$dom->createElement('wsdl:part');
					$partElement->setAttribute('name',$partName);
					$partElement->setAttribute('type',$part[0]);
					$element->appendChild($partElement);
				}
			}
			$dom->documentElement->appendChild($element);
		}
	}

	/*
	 * @param DOMDocument $dom Represents an entire HTML or XML document; serves as the root of the document tree
	 */
	private function addPortTypes($dom)
	{
		$portType=$dom->createElement('wsdl:portType');
		$portType->setAttribute('name',$this->serviceName.'PortType');
		$dom->documentElement->appendChild($portType);
		foreach($this->_operations as $name=>$doc)
			$portType->appendChild($this->createPortElement($dom,$name,$doc));
	}

	/*
	 * @param DOMDocument $dom Represents an entire HTML or XML document; serves as the root of the document tree
	 * @param string $name method name
	 * @param string $doc doc
	 */
	private function createPortElement($dom,$name,$doc)
	{
		$operation=$dom->createElement('wsdl:operation');
		$operation->setAttribute('name',$name);

		$input = $dom->createElement('wsdl:input');
		$input->setAttribute('message', 'tns:'.$name.'Request');
		$output = $dom->createElement('wsdl:output');
		$output->setAttribute('message', 'tns:'.$name.'Response');

		$operation->appendChild($dom->createElement('wsdl:documentation',$doc));
		$operation->appendChild($input);
		$operation->appendChild($output);

		return $operation;
	}

	/*
	 * @param DOMDocument $dom Represents an entire HTML or XML document; serves as the root of the document tree
	 */
	private function addBindings($dom)
	{
		$binding=$dom->createElement('wsdl:binding');
		$binding->setAttribute('name',$this->serviceName.'Binding');
		$binding->setAttribute('type','tns:'.$this->serviceName.'PortType');

		$soapBinding=$dom->createElement('soap:binding');
		$soapBinding->setAttribute('style','rpc');
		$soapBinding->setAttribute('transport','http://schemas.xmlsoap.org/soap/http');
		$binding->appendChild($soapBinding);

		$dom->documentElement->appendChild($binding);

		foreach($this->_operations as $name=>$doc)
			$binding->appendChild($this->createOperationElement($dom,$name));
	}

	/*
	 * @param DOMDocument $dom Represents an entire HTML or XML document; serves as the root of the document tree
	 * @param string $name method name
	 */
	private function createOperationElement($dom,$name)
	{
		$operation=$dom->createElement('wsdl:operation');
		$operation->setAttribute('name', $name);
		$soapOperation = $dom->createElement('soap:operation');
		$soapOperation->setAttribute('soapAction', $this->namespace.'#'.$name);
		$soapOperation->setAttribute('style','rpc');

		$input = $dom->createElement('wsdl:input');
		$output = $dom->createElement('wsdl:output');

		$soapBody = $dom->createElement('soap:body');
		$soapBody->setAttribute('use', 'encoded');
		$soapBody->setAttribute('namespace', $this->namespace);
		$soapBody->setAttribute('encodingStyle', 'http://schemas.xmlsoap.org/soap/encoding/');
		$input->appendChild($soapBody);
		$output->appendChild(clone $soapBody);

		$operation->appendChild($soapOperation);
		$operation->appendChild($input);
		$operation->appendChild($output);

		return $operation;
	}

	/*
	 * @param DOMDocument $dom Represents an entire HTML or XML document; serves as the root of the document tree
	 * @param string $serviceUrl Web service URL
	 */
	private function addService($dom,$serviceUrl)
	{
		$service=$dom->createElement('wsdl:service');
		$service->setAttribute('name', $this->serviceName.'Service');

		$port=$dom->createElement('wsdl:port');
		$port->setAttribute('name', $this->serviceName.'Port');
		$port->setAttribute('binding', 'tns:'.$this->serviceName.'Binding');

		$soapAddress=$dom->createElement('soap:address');
		$soapAddress->setAttribute('location',$serviceUrl);
		$port->appendChild($soapAddress);
		$service->appendChild($port);
		$dom->documentElement->appendChild($service);
	}
}
