<?php
/**
 * This file contains the CWebTestCase class.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

require_once('PHPUnit/Extensions/SeleniumTestCase.php');

/**
 * CWebTestCase is the base class for Web-based functional test case classes.
 *
 * It extends PHPUnit_Extensions_SeleniumTestCase and provides the database
 * fixture management feature like {@link CDbTestCase}.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.test
 * @since 1.1
 */
class CWebTestCase extends PHPUnit_Extensions_SeleniumTestCase
{
	/**
	 * @var array a list of fixtures that should be loaded for all test cases.
	 * The array keys are fixture names, and the array values are either AR class names
	 * or table names. If table names, they must begin with a colon character (e.g. 'Post'
	 * means an AR class, while ':Post' means a table name).
	 */
	public $fixtures=array();

	/**
	 * PHP magic method.
	 * This method is overridden so that named fixture data can be accessed like a normal property.
	 * @param string the property name
	 * @return mixed the property value
	 */
	public function __get($name)
	{
		if(($rows=$this->getFixtureManager()->getRows($name))!==false)
			return $rows;
		else
			throw new Exception("Unknown property '$name' for class '".get_class($this)."'.");
	}

	/**
	 * PHP magic method.
	 * This method is overridden so that named fixture ActiveRecord instances can be accessed in terms of a method call.
	 * @param string method name
	 * @param string method parameters
	 * @return mixed the property value
	 */
	public function __call($name,$params)
	{
		if(isset($params[0]) && ($record=$this->getFixtureManager()->getRecord($name,$params[0]))!==false)
			return $record;
		else
			throw new Exception("Unknown method '$name' for class '".get_class($this)."'.");
	}

	/**
	 * @return CDbFixtureManager the database fixture manager
	 */
	public function getFixtureManager()
	{
		return Yii::app()->getComponent('fixture');
	}

	/**
	 * Sets up the fixture before executing a test method.
	 * If you override this method, make sure the parent implementation is invoked.
	 * Otherwise, the database fixtures will not be managed properly.
	 */
	public function setUp()
	{
		parent::setUp();
		$this->getFixtureManager()->load($this->fixtures);
	}
}
