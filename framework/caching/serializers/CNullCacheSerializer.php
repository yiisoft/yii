<?php
/**
 * CNullCacheSerializer class file.
 *
 * @author Da:Sourcerer <webmaster@dasourcerer.net>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CNullCacheSerializer provides a way to write unaltered contents to
 * the actual cache backend. This is most useful if the cached content consists
 * of scalar value not requiring any form of serialization (i.e. integers, strings
 * or raw binary data).
 * 
 * Take note that this serializer will not be able to serialize complex types (such as arrays
 * or php stuctures) at all. As a side effect, cache dependencies will not work as well.
 * Since all relevant core application components rely on dependency support, you are best advised
 * to set this serializer on a dedicated cache component which is <strong>not</strong> the main
 * cache.
 *  
 * @package system.caching.serializers
 * @since 1.1.11
 */
class CNullCacheSerializer extends CCacheSerializer
{
	public $canSerializeComplexTypes=false;

	public function serialize($value)
	{
		return $value;
	}
	
	public function unserialize($value)
	{
		return $value;
	}
	
}