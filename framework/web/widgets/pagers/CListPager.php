<?php
/**
 * CListPager class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */



/**
 * CListPager displays a dropdown list that contains options leading to different pages of target.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.web.widgets.pagers
 * @since 1.0
 */
class CListPager extends CBasePager
{
	/**
	 * @var string the text shown before page buttons.
	 */
	public $header='Go to page: ';
	/**
	 * @var string the text shown after page buttons.
	 */
	public $footer='';
	/**
	 * @var string the text displayed as a prompt option in the dropdown list. Defaults to null, meaning no prompt.
	 */
	public $promptText;
	/**
	 * @var string the format string used to generate page selection text.
	 * The sprintf function will be used to perform the formatting.
	 */
	public $pageTextFormat;
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
		$pages=array();
		for($i=0;$i<$pageCount;++$i)
			$pages[$this->createPageUrl($i)]=$this->generatePageText($i);
		$selection=$this->createPageUrl($this->getCurrentPage());
		$options=array('onchange'=>'if(this.value!=\'\') {window.location=this.value;};');
		if($this->promptText!==null)
			$options['prompt']=$this->promptText;
		$content=CHtml::dropDownList($this->getId(),$selection,$pages,$options);
		$htmlOptions=$this->htmlOptions;
		if(!isset($htmlOptions['id']))
			$htmlOptions['id']=$this->getId();
		echo CHtml::tag('div',$htmlOptions,$this->header.$content.$this->footer);
	}

	/**
	 * Generates the list option for the specified page number.
	 * You may override this method to customize the option display.
	 * @param integer zero-based page number
	 * @return string the list option for the page number
	 */
	protected function generatePageText($page)
	{
		if($this->pageTextFormat!==null)
			return sprintf($this->pageTextFormat,$page+1);
		else
			return $page+1;
	}
}