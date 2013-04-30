<?php
/**
 * CTagCacheDependency class file.
 *
 * @author Alejandro Pérez <alexgt9@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CTagCacheDependency represents a dependency based on tags.
 *
 * CTagCacheDependency performs dependency checking if the tags selected
 * are still valid.
 * The dependency is reported as unchanged if and only if the tags has not been changed.
 *
 * @author Alejandro Pérez <alexgt9@gmail.com>
 * @package system.caching.dependencies
 * @since 1.0
 */
class CTagCacheDependency extends CCacheDependency
{
	/**
	 * Tags that depends of.
	 * @var array
	 */
	public $tags;

	/**
	 * Constructor.
	 * @param array $tags to be dependant of.
	 */
	public function __construct( array $tags )
	{
		$this->tags = array_fill_keys( $tags, 1 );
	}

	/**
	 * Generates the data needed to determine if dependency has been changed.
	 * This method returns the array with the version of every tag.
	 * @return mixed the data needed to determine if dependency has been changed.
	 */
	protected function generateDependentData()
	{
		$cache = Yii::app()->cache;
		$tagsVersion = $cache->get( $cache->tagsDependencyId );

		if ( $tagsVersion == false ) 
		{
			$tagsVersion = array();
		}
		$present = array_intersect_key( $tagsVersion, $this->tags );
		$missing = array_diff_key( $this->tags, $tagsVersion );

		if ( !empty( $missing ) ) 
		{
			$cache->set( $cache->tagsDependencyId, array_merge( $tagsVersion, $missing ) );
		}

		return array_merge( $present, $missing );
	}
}
