<?php

/**
 * CLinkPageSizer displays a list of hyperlinks that lead to different page sizes.
 */
class CLinkPageSizer extends CBasePageSizer
{
	const CSS_SELECTED_PAGE_SIZE='selected';

	/**
	 * @var string the CSS class for the selected page size buttons. Defaults to 'selected'.
	 * @since 1.1.11
	 */
	public $selectedPageSizeCssClass=self::CSS_SELECTED_PAGE_SIZE;
	/**
	 * @var string the text shown before page size buttons. Defaults to 'Items per page: '.
	 */
	public $header;
	/**
	 * @var string the text shown after page size buttons.
	 */
	public $footer='';
	/**
	 * @var mixed the CSS file used for the widget. Defaults to null, meaning
	 * using the default CSS file included together with the widget.
	 * If false, no CSS file will be used. Otherwise, the specified CSS file
	 * will be included when using this widget.
	 */
	public $cssFile;
	/**
	 * @var array HTML attributes for the page sizer container tag.
	 */
	public $htmlOptions=array();

	/**
	 * Initializes the page sizer by setting some default property values.
	 */
	public function init()
	{
		parent::init();
		if($this->header===null)
			$this->header=Yii::t('yii','Items per page: ');


		if(!isset($this->htmlOptions['id']))
			$this->htmlOptions['id']=$this->getId();
		if(!isset($this->htmlOptions['class']))
			$this->htmlOptions['class']='yiiPager';
	}

	/**
	 * Executes the widget.
	 * This overrides the parent implementation by displaying the generated page buttons.
	 */
	public function run()
	{
		$this->registerClientScript();
		$buttons=$this->createPageSizeButtons();
		if(empty($buttons))
			return;
		echo $this->header;
		echo CHtml::tag('ul',$this->htmlOptions,implode("\n",$buttons));
		echo $this->footer;
	}

	/**
	 * Creates the page buttons.
	 * @return array a list of page buttons (in HTML code).
	 */
	protected function createPageSizeButtons()
	{
		if(count($this->availableSizes) == 0)
			return array();
		$currentPageSize=$this->getPageSize();
		foreach ($this->availableSizes as $size=>$label) {
			$buttons[]=$this->createPageSizeButton($label,$size,$size==$currentPageSize);
		}
		return $buttons;
	}

	/**
	 * Creates a page size button.
	 * You may override this method to customize the page size buttons.
	 * @param string $label the text label for the button
	 * @param integer $pageSize the page size
	 * @param boolean $selected whether this page size button is selected
	 * @return string the generated button
	 */
	protected function createPageSizeButton($label,$pageSize,$selected)
	{
		if($selected)
			$class.=' '.$this->selectedPageSizeCssClass;
		return '<li class="'.$class.'">'.CHtml::link($label,$this->createPageSizeUrl($pageSize)).'</li>';
	}

	/**
	 * Registers the needed client scripts (mainly CSS file).
	 */
	public function registerClientScript()
	{
		if($this->cssFile!==false)
			self::registerCssFile($this->cssFile);
	}

	/**
	 * Registers the needed CSS file.
	 * @param string $url the CSS URL. If null, a default CSS URL will be used.
	 */
	public static function registerCssFile($url=null)
	{
		if($url===null)
			$url=CHtml::asset(Yii::getPathOfAlias('system.web.widgets.pagers.pager').'.css');
		Yii::app()->getClientScript()->registerCssFile($url);
	}
}
