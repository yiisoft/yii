<?php
/**
 * CCacheSerializer class file.
 *
 * @author Da:Sourcerer <webmaster@dasourcerer.net>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CCacheSerilizer is the base class for all cache serializers. It is the default choice as it
 * achieves serialization through PHP's native {@link http://php.net/manual/function.serialize.php serialize()}
 * and {@link http://php.net/manual/function.unserialize.php unserialize} functions.
 * 
 * This class may be extended to provide other means of serialization.
 * 
 * <strong>Note:</strong> As different serialization formats are usually incompatible and unserialization methods
 * tend to handle parse errors in non-graceful ways, it is advisable to purge caches before switching serializers
 * to prevent errors.
 * 
 * @package system.caching.serializers
 * @since 1.1.11
 */
class CCacheSerializer extends CComponent
{
	/**
	 * @var boolean signals CCache whether this serializer can handle complex types or not.
	 */
	public $canSerializeComplexTypes=true;
	
	/**
	 * Serializes the value before it will be stored in the actual cache backend.
	 * Child classes may override this method to change the way the value is being serialized. 
	 * @param mixed $value the unserialized representation of the value
	 * @return string the serialized representation of the value
	 **/
	public function serialize($value)
	{
		return serialize($value);
	}
	
	/**
	 * Unserializes the value after it was retrieved from the actual cache backend.
	 * Child classes may override this method to change the way the value is being unserialized.
	 * @param string $value the serialized representation of the value
	 * @return mixed the unserialized representation of the value
	 **/
	public function unserialize($value)
	{
		return unserialize($value);
	}
	
}