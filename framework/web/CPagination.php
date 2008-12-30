<?php
/**
 * CPagination class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CPagination represents information relevant to pagination.
 *
 * When data needs to be rendered in multiple pages, we can use CPagination to
 * represent information such as {@link getItemCount total item count},
 * {@link getPageSize page size}, {@link getCurrentPage current page}, etc.
 * These information can be passed to {@link CBasePager pagers} to render
 * pagination buttons or links.
 *
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.web
 * @since 1.0
 */
class CPagination extends CComponent
{
	/**
	 * The default page size.
	 */
	const DEFAULT_PAGE_SIZE=10;
	/**
	 * @var string name of the GET variable storing the current page index. Defaults to 'page'.
	 */
	public $pageVar='page';
	/**
	 * @var string the route (controller ID and action ID) for displaying the paged contents.
	 * Defaults to empty string, meaning using the current route.
	 */
	public $route='';

	private $_pageSize=self::DEFAULT_PAGE_SIZE;
	private $_itemCount=0;
	private $_currentPage;

	/**
	 * Constructor.
	 * @param integer total number of items.
	 * @since 1.0.1
	 */
	public function __construct($itemCount=0)
	{
		$this->setItemCount($itemCount);
	}

	/**
	 * @return integer number of items in each page. Defaults to 10.
	 */
	public function getPageSize()
	{
		return $this->_pageSize;
	}

	/**
	 * @param integer number of items in each page
	 */
	public function setPageSize($value)
	{
		if(($this->_pageSize=$value)<=0)
			$this->_pageSize=self::DEFAULT_PAGE_SIZE;
	}

	/**
	 * @return integer total number of items. Defaults to 0.
	 */
	public function getItemCount()
	{
		return $this->_itemCount;
	}

	/**
	 * @param integer total number of items.
	 */
	public function setItemCount($value)
	{
		if(($this->_itemCount=$value)<0)
			$this->_itemCount=0;
	}

	/**
	 * @return integer number of pages
	 */
	public function getPageCount()
	{
		return (int)(($this->_itemCount+$this->_pageSize-1)/$this->_pageSize);
	}

	/**
	 * @param boolean whether to recalculate the current page based on the page size and item count.
	 * @return integer the zero-based index of the current page. Defaults to 0.
	 */
	public function getCurrentPage($recalculate=true)
	{
		if($this->_currentPage===null || $recalculate)
		{
			if(isset($_GET[$this->pageVar]))
			{
				$this->_currentPage=(int)$_GET[$this->pageVar]-1;
				$pageCount=$this->getPageCount();
				if($this->_currentPage>=$pageCount)
					$this->_currentPage=$pageCount-1;
				if($this->_currentPage<0)
					$this->_currentPage=0;
			}
			else
				$this->_currentPage=0;
		}
		return $this->_currentPage;
	}

	/**
	 * @param integer the zero-based index of the current page.
	 */
	public function setCurrentPage($value)
	{
		$this->_currentPage=$value;
	}

	/**
	 * Creates the URL suitable for pagination.
	 * This method is mainly called by pagers when creating URLs used to
	 * perform pagination. The default implementation is to call
	 * the controller's createUrl method with the page information.
	 * You may override this method if your URL scheme is not the same as
	 * the one supported by the controller's createUrl method.
	 * @param CController the controller that will create the actual URL
	 * @param integer the page that the URL should point to. This is a zero-based index.
	 * @return string the created URL
	 */
	public function createPageUrl($controller,$page)
	{
		$params=($this->route==='')?$_GET:array();
		if($page>0) // page 0 is the default
			$params[$this->pageVar]=$page+1;
		else
			unset($params[$this->pageVar]);
		return $controller->createUrl($this->route,$params);
	}

	/**
	 * Applies LIMIT and OFFSET to the specified query criteria.
	 * @param CDbCriteria the query criteria that should be applied with the limit
	 * @since 1.0.1
	 */
	public function applyLimit($criteria)
	{
		$criteria->limit=$this->pageSize;
		$criteria->offset=$this->currentPage*$this->pageSize;
	}
}