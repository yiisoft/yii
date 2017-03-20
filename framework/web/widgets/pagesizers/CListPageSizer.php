<?php

/**
 * CListPageSizer displays a dropdown list of page sizes
 */
class CListPageSizer extends CBasePageSizer
{
	/**
	 * @var string the text shown before page size buttons. Defaults to 'Items per page: '.
	 */
	public $header;
	/**
	 * @var string the text shown after page size buttons.
	 */
	public $footer='';
	/**
	 * @var string the text displayed as a prompt option in the dropdown list. Defaults to null, meaning no prompt.
	 */
	public $promptText;
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
		if($this->promptText!==null)
			$this->htmlOptions['prompt']=$this->promptText;
		if(!isset($this->htmlOptions['onchange']))
			$this->htmlOptions['onchange']="if(this.value!='') {window.location=this.value;};";
	}

	/**
	 * Executes the widget.
	 * This overrides the parent implementation by displaying the generated page size buttons.
	 */
	public function run()
	{
		if(count($this->availableSizes) == 0)
			return;
		$pageSizes=array();
		foreach ($this->availableSizes as $size=>$label) {
			$pageSizes[$this->createPageSizeUrl($size)]=$label;
		}
		$selection=$this->createPageSizeUrl($this->getPageSize());
		echo $this->header;
		echo CHtml::dropDownList($this->getId(),$selection,$pageSizes,$this->htmlOptions);
		echo $this->footer;
	}
}
