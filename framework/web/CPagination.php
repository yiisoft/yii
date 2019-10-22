<?php
/**
 * CPagination class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
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
 * Example:
 *
 * Controller action:
 * <pre>
 * function actionIndex(){
 *     $criteria=new CDbCriteria();
 *     $count=Article::model()->count($criteria);
 *     $pages=new CPagination($count);
 *
 *     // results per page
 *     $pages->pageSize=10;
 *     $pages->applyLimit($criteria);
 *     $models=Article::model()->findAll($criteria);
 *
 *     $this->render('index', array(
 *     'models' => $models,
 *          'pages' => $pages
 *     ));
 * }
 * </pre>
 *
 * View:
 * <pre>
 * <?php foreach($models as $model): ?>
 *     // display a model
 * <?php endforeach; ?>
 *
 * // display pagination
 * <?php $this->widget('CLinkPager', array(
 *     'pages' => $pages,
 * )) ?>
 * </pre>
 *
 * @property int $pageSize Number of items in each page. Defaults to 10.
 * @property int $itemCount Total number of items. Defaults to 0.
 * @property int $pageCount Number of pages.
 * @property int $currentPage The zero-based index of the current page. Defaults to 0.
 * @property int $offset The offset of the data. This may be used to set the
 * OFFSET value for a SQL statement for fetching the current page of data.
 * @property int $limit The limit of the data. This may be used to set the
 * LIMIT value for a SQL statement for fetching the current page of data.
 * This returns the same value as {@link pageSize}.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
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
	/**
	 * @var array of parameters (name=>value) that should be used instead of GET when generating pagination URLs.
	 * Defaults to null, meaning using the currently available GET parameters.
	 */
	public $params;
	/**
	 * @var bool whether to ensure {@link currentPage} is returning a valid page number.
	 * When this property is true, the value returned by {@link currentPage} will always be between
	 * 0 and ({@link pageCount}-1). Because {@link pageCount} relies on the correct value of {@link itemCount},
	 * it means you must have knowledge about the total number of data items when you want to access {@link currentPage}.
	 * This is fine for SQL-based queries, but may not be feasible for other kinds of queries (e.g. MongoDB).
	 * In those cases, you may set this property to be false to skip the validation (you may need to validate yourself then).
	 * Defaults to true.
	 * @since 1.1.4
	 */
	public $validateCurrentPage=true;

	private $_pageSize=self::DEFAULT_PAGE_SIZE;
	private $_itemCount=0;
	private $_currentPage;

	/**
	 * Constructor.
	 * @param int $itemCount total number of items.
	 */
	public function __construct($itemCount=0)
	{
		$this->setItemCount($itemCount);
	}

	/**
	 * @return int number of items in each page. Defaults to 10.
	 */
	public function getPageSize()
	{
		return $this->_pageSize;
	}

	/**
	 * @param int $value number of items in each page
	 *
	 * @return void
	 */
	public function setPageSize($value)
	{
	    $value = (int)$value;
		if(($this->_pageSize=$value)<=0)
			$this->_pageSize=self::DEFAULT_PAGE_SIZE;
	}

	/**
	 * @return int total number of items. Defaults to 0.
	 */
	public function getItemCount()
	{
		return $this->_itemCount;
	}

	/**
	 * @param int $value total number of items.
	 *
	 * @return void
	 */
	public function setItemCount($value)
	{
	    $value = (int)$value;
		if(($this->_itemCount=$value)<0)
			$this->_itemCount=0;
	}

	/**
	 * @return int number of pages
	 */
	public function getPageCount()
	{
		return (int)(($this->_itemCount+$this->_pageSize-1)/$this->_pageSize);
	}

    /**
     * @return int the zero-based index of the current page. Defaults to 0.
     */
    public function getCurrentPage()
    {
        if ($this->_currentPage === null) {
            $this->_currentPage = 0;
            if (isset($_GET[$this->pageVar])) {
                $this->_currentPage = (int)$_GET[$this->pageVar] - 1;
            }
        }

        if ($this->validateCurrentPage) {
            $pageCount = $this->getPageCount();
            if ($this->_currentPage >= $pageCount) {
                $this->_currentPage = $pageCount - 1;
            }
        }
        if ($this->_currentPage < 0) {
            $this->_currentPage = 0;
        }

        return $this->_currentPage;
    }

    /**
	 * @param int $value the zero-based index of the current page.
	 *
	 * @return void
	 */
	public function setCurrentPage($value)
	{
	    $value = (int)$value;
		$this->_currentPage=$value;
	}

	/**
	 * Creates the URL suitable for pagination.
	 * This method is mainly called by pagers when creating URLs used to
	 * perform pagination. The default implementation is to call
	 * the controller's createUrl method with the page information.
	 * You may override this method if your URL scheme is not the same as
	 * the one supported by the controller's createUrl method.
	 * @param CController $controller the controller that will create the actual URL
	 * @param int $page the page that the URL should point to. This is a zero-based index.
	 * @return string the created URL
	 */
	public function createPageUrl($controller,$page)
	{
		$params=$this->params===null ? $_GET : $this->params;
		if($page>0) // page 0 is the default
			$params[$this->pageVar]=$page+1;
		else
			unset($params[$this->pageVar]);
		return $controller->createUrl($this->route,$params);
	}

	/**
	 * Applies LIMIT and OFFSET to the specified query criteria.
	 *
	 * @param CDbCriteria $criteria the query criteria that should be applied with the limit
	 *
	 * @return void
	 */
	public function applyLimit(CDbCriteria $criteria)
	{
		$criteria->limit=$this->getLimit();
		$criteria->offset=$this->getOffset();
	}

	/**
	 * @return int the offset of the data. This may be used to set the
	 * OFFSET value for a SQL statement for fetching the current page of data.
	 * @since 1.1.0
	 */
	public function getOffset()
	{
		return $this->getCurrentPage()*$this->getPageSize();
	}

	/**
	 * @return int the limit of the data. This may be used to set the
	 * LIMIT value for a SQL statement for fetching the current page of data.
	 * This returns the same value as {@link pageSize}.
	 * @since 1.1.0
	 */
	public function getLimit()
	{
		return $this->getPageSize();
	}
}
