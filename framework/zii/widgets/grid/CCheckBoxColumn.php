<?php
/**
 * CCheckBoxColumn class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

Yii::import('zii.widgets.grid.CGridColumn');

/**
 * CCheckBoxColumn represents a grid view column of checkboxes.
 *
 * CCheckBoxColumn supports single selection and multiple selection. The mode is determined according
 * to {@link CGridView::selectableRows}. When in multiple selection mode, the header cell will display
 * an additional checkbox, clicking on which will check or uncheck all of the checkboxes in the data cells.
 *
 * By default, the checkboxes rendered in data cells will have the values that are the same as
 * the key values of the data model. One may change this by setting either {@link name} or
 * {@link value}.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package zii.widgets.grid
 * @since 1.1
 */
class CCheckBoxColumn extends CGridColumn
{
	/**
	 * @var string the attribute name of the data model. The corresponding attribute value will be rendered
	 * in each data cell as the checkbox value. Note that if {@link value} is specified, this property will be ignored.
	 * @see value
	 */
	public $name;
	/**
	 * @var string a PHP expression that will be evaluated for every data cell and whose result will be rendered
	 * in each data cell as the checkbox value. In this expression, the variable
	 * <code>$row</code> the row number (zero-based); <code>$data</code> the data model for the row;
	 * and <code>$this</code> the column object.
	 */
	public $value;
	/**
	 * @var string a PHP expression that will be evaluated for every data cell and whose result will
	 * determine if checkbox for each data cell is checked. In this expression, the variable
	 * <code>$row</code> the row number (zero-based); <code>$data</code> the data model for the row;
	 * and <code>$this</code> the column object.
	 * @since 1.1.4
	 */
	public $checked;
	/**
	 * @var array the HTML options for the data cell tags.
	 */
	public $htmlOptions=array('class'=>'checkbox-column');
	/**
	 * @var array the HTML options for the header cell tag.
	 */
	public $headerHtmlOptions=array('class'=>'checkbox-column');
	/**
	 * @var array the HTML options for the footer cell tag.
	 */
	public $footerHtmlOptions=array('class'=>'checkbox-column');
	/**
	 * @var array the HTML options for the checkboxes.
	 */
	public $checkBoxHtmlOptions=array();

	/**
	 * Initializes the column.
	 * This method registers necessary client script for the checkbox column.
	 */
	public function init()
	{
		if(isset($this->checkBoxHtmlOptions['name']))
			$name=$this->checkBoxHtmlOptions['name'];
		else
		{
			$name=$this->id;
			if(substr($name,-2)!=='[]')
				$name.='[]';
			$this->checkBoxHtmlOptions['name']=$name;
		}
		$name=strtr($name,array('['=>"\\[",']'=>"\\]"));
		if($this->grid->selectableRows==1)
			$one="\n\tjQuery(\"input:not(#\"+$(this).attr('id')+\")[name='$name']\").attr('checked',false);";
		else
			$one='';
		$js=<<<EOD
jQuery('#{$this->id}_all').live('click',function() {
	var checked=this.checked;
	jQuery("input[name='$name']").each(function() {
		this.checked=checked;
	});
});
jQuery("input[name='$name']").live('click', function() {
	jQuery('#{$this->id}_all').attr('checked', jQuery("input[name='$name']").length==jQuery("input[name='$name']:checked").length);{$one}
});
EOD;
		Yii::app()->getClientScript()->registerScript(__CLASS__.'#'.$this->id,$js);
	}

	/**
	 * Renders the header cell content.
	 * This method will render a checkbox in the header when {@link CGridView::selectableRows} is greater than 1.
	 */
	protected function renderHeaderCellContent()
	{
		if($this->grid->selectableRows>1)
			echo CHtml::checkBox($this->id.'_all',false);
		else
			parent::renderHeaderCellContent();
	}

	/**
	 * Renders the data cell content.
	 * This method renders a checkbox in the data cell.
	 * @param integer $row the row number (zero-based)
	 * @param mixed $data the data associated with the row
	 */
	protected function renderDataCellContent($row,$data)
	{
		if($this->value!==null)
			$value=$this->evaluateExpression($this->value,array('data'=>$data,'row'=>$row));
		else if($this->name!==null)
			$value=CHtml::value($data,$this->name);
		else
			$value=$this->grid->dataProvider->keys[$row];

		$checked = false;
		if($this->checked!==null)
			$checked=$this->evaluateExpression($this->checked,array('data'=>$data,'row'=>$row));

		$options=$this->checkBoxHtmlOptions;
		$name=$options['name'];
		unset($options['name']);
		$options['value']=$value;
		$options['id']=$this->id.'_'.$row;
		echo CHtml::checkBox($name,$checked,$options);
	}
}
