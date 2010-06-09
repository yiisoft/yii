<?php
/**
 * CActiveDataProvider implements a data provider based on ActiveRecord.
 *
 * CActiveDataProvider provides data in terms of ActiveRecord objects which are
 * of class {@link modelClass}. It uses the AR {@link CActiveRecord::findAll} method
 * to retrieve the data from database. The {@link criteria} property can be used to
 * specify various query options, such as conditions, sorting, pagination, etc.
 *
 * CActiveDataProvider may be used in the following way:
 * <pre>
 * $dataProvider=new CActiveDataProvider('Post', array(
 *     'criteria'=>array(
 *         'condition'=>'status=1',
 *         'order'=>'create_time DESC',
 *         'with'=>array('author'),
 *     ),
 *     'pagination'=>array(
 *         'pageSize'=>20,
 *     ),
 * ));
 * // $dataProvider->getData() will return a list of Post objects
 * </pre>
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.web
 * @since 1.1
 */
class CActiveDataProvider extends CDataProvider
{
	/**
	 * @var string the primary ActiveRecord class name. The {@link getData()} method
	 * will return a list of objects of this class.
	 */
	public $modelClass;
	/**
	 * @var string the name of key attribute for {@link modelClass}. If not set,
	 * it means the primary key of the corresponding database table will be used.
	 */
	public $keyAttribute;

	private $_criteria;

	/**
	 * Constructor.
	 * @param string the model class. This will be assigned to the {@link modelClass} property.
	 * @param array configuration (name=>value) to be applied to this data provider.
	 * Any public properties of the data provider can be configured via this parameter
	 */
	public function __construct($modelClass,$config=array())
	{
		$this->modelClass=$modelClass;
		$this->setId($modelClass);
		foreach($config as $key=>$value)
			$this->$key=$value;
	}

	/**
	 * @return CDbCriteria the query criteria
	 */
	public function getCriteria()
	{
		if($this->_criteria===null)
			$this->_criteria=new CDbCriteria;
		return $this->_criteria;
	}

	/**
	 * @param mixed the query criteria. This can be either a CDbCriteria object or an array
	 * representing the query criteria.
	 */
	public function setCriteria($value)
	{
		$this->_criteria=$value instanceof CDbCriteria ? $value : new CDbCriteria($value);
	}

	/**
	 * @return CSort the sorting object. If this is false, it means the sorting is disabled.
	 */
	public function getSort()
	{
		if(($sort=parent::getSort())!==false)
			$sort->modelClass=$this->modelClass;
		return $sort;
	}

	private $_model;

	/**
	 * Returns the AR finder that will be used in {@link fetchData} and {@link calculateTotalItemCount}.
	 * The default implementation will return the finder specified by {@link modelClass}.
	 * You may change this behavior by configuring {@link setModel model} with a customized finder.
	 * For example, <code>Post::model()->published()</code> can be assigned to the property
	 * so that the "published" scope is applied before querying.
	 * @return CActiveRecord the AR finder for {@link modelClass}
	 * @since 1.1.3
	 */
	public function getModel()
	{
		if($this->_model===null)
			$this->_model=CActiveRecord::model($this->modelClass);
		return $this->_model;
	}

	/**
	 * Sets the AR finder that will be used in {@link fetchData} and {@link calculateTotalItemCount}.
	 * @param CActiveRecord the AR finder for {@link modelClass}
	 * @since 1.1.3
	 * @see getModel
	 */
	public function setModel($model)
	{
		$this->_model=$model;
	}

	/**
	 * Fetches the data from the persistent data storage.
	 * @return array list of data items
	 */
	protected function fetchData()
	{
		$criteria=clone $this->getCriteria();
		if(($pagination=$this->getPagination())!==false)
		{
			$pagination->setItemCount($this->getTotalItemCount());
			$pagination->applyLimit($criteria);
		}
		if(($sort=$this->getSort())!==false)
			$sort->applyOrder($criteria);

		$finder=clone $this->getModel();
		return $finder->findAll($criteria);
	}

	/**
	 * Fetches the data item keys from the persistent data storage.
	 * @return array list of data item keys.
	 */
	protected function fetchKeys()
	{
		$keys=array();
		if($this->keyAttribute===null)
		{
			foreach($this->getData() as $i=>$data)
				$keys[$i]=$data->getPrimaryKey();
		}
		else
		{
			foreach($this->getData() as $i=>$data)
				$keys[$i]=$data->{$this->keyAttribute};
		}
		return $keys;
	}

	/**
	 * Calculates the total number of data items.
	 * @return integer the total number of data items.
	 */
	protected function calculateTotalItemCount()
	{
		$finder=clone $this->getModel();
		return $finder->count($this->getCriteria());
	}
}
