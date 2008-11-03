<?php
/**
 * CLinkPager class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CLinkPager displays a list of hyperlinks that lead to different pages of target.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.web.widgets.pagers
 * @since 1.0
 */
class CLinkPager extends CBasePager
{
	/**
	 * @var integer maximum number of page buttons that can be displayed. Defaults to 10.
	 */
	public $maxButtonCount=10;
	/**
	 * @var string the text label for the next page button. Defaults to 'Next >>'.
	 * Note that the label must be HTML encoded.
	 */
	public $nextPageLabel='Next &gt;&gt;';
	/**
	 * @var string the text label for the previous page button. Defaults to '<< Prev'.
	 * Note that the label must be HTML encoded.
	 */
	public $prevPageLabel='&lt;&lt; Prev';
	/**
	 * @var string the text label for the first page button. Defaults to '|<< First'.
	 * Note that the label must be HTML encoded.
	 */
	public $firstPageLabel='|&lt;&lt; First';
	/**
	 * @var string the text label for the last page button. Defaults to 'Last >>|'.
	 * Note that the label must be HTML encoded.
	 */
	public $lastPageLabel='Last &gt;&gt;|';
	/**
	 * @var boolean whether to show the first page button. Defaults to false.
	 * Note that the label must be HTML encoded.
	 */
	public $showFirstPageButton=false;
	/**
	 * @var boolean whether to show the last page button. Defaults to false.
	 */
	public $showLastPageButton=false;
	/**
	 * @var string separator between page buttons. Defaults to "\n".
	 */
	public $buttonSeparator="\n";
	/**
	 * @var string the text shown before page buttons.
	 */
	public $header='Go to page: ';
	/**
	 * @var string the text shown after page buttons.
	 */
	public $footer='';
	/**
	 * @var array HTML attributes for the enclosing 'div' tag.
	 */
	public $htmlOptions=array();


	/**
	 * Executes the widget.
	 * This overrides the parent implementation by displaying the generated page buttons.
	 */
	public function run()
	{
		if(($pageCount=$this->getPageCount())<=1)
			return;

		list($beginPage,$endPage)=$this->getPageRange();
		$currentPage=$this->getCurrentPage();
		$controller=$this->getController();
		$params=$_GET;
		$buttons=array();

		if($beginPage>0 && $this->showFirstPageButton)
			$buttons[]=$this->createPageButton($this->firstPageLabel,0,$currentPage);
		if($currentPage>0)
			$buttons[]=$this->createPageButton($this->prevPageLabel,$currentPage-1,$currentPage);

		for($i=$beginPage;$i<=$endPage;++$i)
			$buttons[]=$this->createPageButton($i+1,$i,$currentPage);

		if($currentPage<$pageCount-1)
			$buttons[]=$this->createPageButton($this->nextPageLabel,$currentPage+1,$currentPage);
		if($endPage<$pageCount-1 && $this->showLastPageButton)
			$buttons[]=$this->createPageButton($this->lastPageLabel,$pageCount-1,$currentPage);

		$content=implode($this->buttonSeparator,$buttons);
		$htmlOptions=$this->htmlOptions;
		if(!isset($htmlOptions['id']))
			$htmlOptions['id']=$this->getId();
		echo CHtml::tag('div',$htmlOptions,$this->header.$content.$this->footer);
	}

	/**
	 * @return array the begin and end pages that need to be displayed.
	 */
	protected function getPageRange()
	{
		$currentPage=$this->getCurrentPage();
		$pageCount=$this->getPageCount();
		$buttonCount=$this->maxButtonCount > $pageCount ? $pageCount : $this->maxButtonCount;

		$beginPage=0;
		$endPage=$buttonCount-1;
		if($currentPage>$endPage)
		{
			$beginPage=((int)($currentPage/$this->maxButtonCount))*$this->maxButtonCount;
			if(($endPage=$beginPage+$this->maxButtonCount-1)>=$pageCount)
				$endPage=$pageCount-1;
		}
		return array($beginPage,$endPage);
	}

	/**
	 * Creates a page button.
	 * You may override this method to customize the page buttons.
	 * Hint: you can use the label parameter to determine which type of button
	 * is being created. For example, if the label is the same as {@link firstPageLabel},
	 * then it is creating the first page button.
	 * @param string the text label for the button
	 * @param integer the page number
	 * @param integer the current page number
	 * @return string the generated button
	 */
	protected function createPageButton($label,$page,$currentPage)
	{
		if($page===$currentPage)
			return '<span>'.$label.'</span>';
		else
			return '<span>'.CHtml::link($label,$this->createPageUrl($page)).'</span>';
	}
}
