<?php
/**
 * CigbinaryCacheSerializer class file.
 *
 * @author Da:Sourcerer <webmaster@dasourcerer.net>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CIgbinaryCacheSerializer provides serialization through the {@link http://pecl.php.net/package/igbinary igbinary} extension.
 * 
 * The igbinary serialization format sacrifices human readability in exchange for a more compact, binary output, which yields
 * a speed and space benefit over PHP's native serialization. The igbinary extension must be installed and loaded to make use of this
 * serializer.
 * 
 * @package system.caching.serializers
 * @since 1.1.11
 */
class CIgbinaryCacheSerializer extends CCacheSerializer
{
	/**
	 * Initializes this component.
	 * @throws CException if the igbinary extension is not loaded or enabled.
	 */
	public function init()
	{
		parent::init();
		if(!extension_loaded('igbinary'))
			throw new CException(Yii::t('yii','CIgbinaryCacheSerializer requires PHP igbinary extension to be loaded.'));
	}
	
	public function serialize($value)
	{
		return igbinary_serialize($value);
	}
	
	public function unserialize($value)
	{
		return igbinary_unserialize($value);
	}
}