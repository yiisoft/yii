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
	const DEFAULT_PAGE_SIZE=20;
	/**
	 * @var string name of the GET variable storing the current page index. Defaults to 'page'.
	 */
	public $pageVar='page';

	private $_pageSize=self::DEFAULT_PAGE_SIZE;
	private $_itemCount=0;
	private $_currentPage;

	/**
	 * @return integer number of items in each page. Defaults to 20.
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
				$this->_currentPage=(int)$_GET[$this->pageVar];
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
	 * @param CController the controller that will create the actual URL
	 * @param integer the page that the URL should point to.
	 * @return string the created URL
	 */
	public function createPageUrl($controller,$page)
	{
		$params=$_GET;
		$params[$this->pageVar]=$page;
		return $controller->createUrl('',$params);
	}
}