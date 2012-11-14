<?php
/**
 * CDataProviderIterator class file.
 *
 * @author Charles Pick <charles.pick@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CDataProviderIterator allows iteration over large data sets without holding the entire set in memory.
 *
 * CDataProviderIterator iterates over the results of a data provider, starting at the first page
 * of results and ending at the last page. It is usually only suited for use with CActiveDataProvider.
 *
 * For example, the following code will iterate through all registered users without
 * running out of memory, even if there are millions of users in the database.
 * <pre>
 * $dataProvider = new CActiveDataProvider("User");
 * $iterator = new CDataProviderIterator($dataProvider);
 * foreach($iterator as $user) {
 *	 echo $user->name."\n";
 * }
 * </pre>
 *
 * @property array $items the currently loaded items
 * @property integer $currentIndex the 0 based current index
 * @property integer $totalItems the total number of items in the iterator
 *
 * @author Charles Pick <charles.pick@gmail.com>
 * @package system.web
 * @since 1.1.11
 */
class CDataProviderIterator implements Iterator, Countable
{

	/**
	 * The data provider to iterate over
	 *
	 * @var CDataProvider
	 */
	public $dataProvider;

	/**
	 * The current index
	 *
	 * @var integer
	 */
	private $_currentIndex=-1;

	/**
	 * The current page in the pagination
	 *
	 * @var integer
	 */
	private $_currentPage=0;

	/**
	 * The total number of items
	 *
	 * @var integer
	 */
	private $_totalItems=-1;

	/**
	 * The current set of items
	 *
	 * @var array
	 */
	private $_items;

	/**
	 * Constructor. Sets the data provider to iterator over
	 *
	 * @param CDataProvider $dataProvider the data provider to iterate over
	 */
	public function __construct(CDataProvider $dataProvider)
	{
		$this->dataProvider=$dataProvider;
		$this->_totalItems=$dataProvider->getTotalItemCount();
	}

	/**
	 * Sets the current index position
	 *
	 * @param integer $currentIndex the current index positon
	 */
	public function setCurrentIndex($currentIndex)
	{
		$this->_currentIndex=$currentIndex;
	}

	/**
	 * Gets the zero based current index position
	 *
	 * @return integer index position
	 */
	public function getCurrentIndex()
	{
		return $this->_currentIndex;
	}

	/**
	 * Gets the current set of items to iterate over
	 *
	 * @return array the current set items to iterate over
	 */
	public function getItems()
	{
		return $this->_items;
	}

	/**
	 * Sets the total number of items to iterate over
	 *
	 * @param int $totalItems the total number of items to iterate over
	 */
	public function setTotalItems($totalItems)
	{
		$this->_totalItems=$totalItems;
	}

	/**
	 * Gets the total number of items to iterate over
	 *
	 * @return integer the total number of items to iterate over
	 */
	public function getTotalItems()
	{
		return $this->_totalItems;
	}

	/**
	 * Loads the page of results
	 *
	 * @return array the items from the next page of results
	 */
	protected function loadPage()
	{
		$this->dataProvider->getPagination()->setCurrentPage($this->_currentPage);
		return $this->_items=$this->dataProvider->getData(true);
	}

	/**
	 * Gets the current item in the list.
	 * This method is required by the Iterator interface
	 *
	 * @return mixed the current item in the list
	 */
	public function current()
	{
		return $this->_items[$this->_currentIndex];
	}

	/**
	 * Gets the key of the current item.
	 * This method is required by the Iterator interface
	 *
	 * @return integer the key of the current item
	 */
	public function key()
	{
		$pagination=$this->dataProvider->getPagination();
		$pageSize=$pagination->getPageSize();
		return $this->_currentPage*$pageSize+$this->_currentIndex;
	}

	/**
	 * Moves the pointer to the next item in the list.
	 * This method is required by the Iterator interface
	 */
	public function next()
	{
		$pagination=$this->dataProvider->getPagination();
		$this->_currentIndex++;
		if($this->_currentIndex>=$pagination->getPageSize())
		{
			$this->_currentPage++;
			$this->_currentIndex=0;
			$this->loadPage();
		}
	}

	/**
	 * Rewinds the iterator to the start of the list.
	 * This method is required by the Iterator interface
	 */
	public function rewind()
	{
		$this->_currentIndex=0;
		$this->_currentPage=0;
		$this->loadPage();
	}

	/**
	 * Checks if the current position is valid or not.
	 * This method is required by the Iterator interface
	 *
	 * @return boolean true if this index is valid
	 */
	public function valid()
	{
		return $this->key()<$this->_totalItems;
	}

	/**
	 * Gets the total number of items in the dataProvider
	 * This method is required by the Countable interface
	 *
	 * @return integer the total number of items
	 */
	public function count()
	{
		return $this->_totalItems;
	}
}