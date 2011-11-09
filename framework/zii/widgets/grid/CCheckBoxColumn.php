<?php
/**
 * CCheckBoxColumn class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

Yii::import('zii.widgets.grid.CGridColumn');

/**
 * CCheckBoxColumn represents a grid view column of checkboxes.
 *
 * CCheckBoxColumn supports no selection (read-only), single selection and multiple selection.
 * The mode is determined according to {@link selectableRows}. When in multiple selection mode, the header cell will display
 * an additional checkbox, clicking on which will check or uncheck all of the checkboxes in the data cells.
 *
 * Additionally selecting a checkbox can select a grid view row (depending on {@link CGridView::selectableRows} value) if
 * {@link selectableRows} is null (default).
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
	 * @var integer the number of rows that can be checked.
	 * Possible values:
	 * <ul>
	 * <li>0 - the state of the checkbox cannot be changed (read-only mode)</li>
	 * <li>1 - only one row can be checked. Checking a checkbox has nothing to do with selecting the row</li>
	 * <li>2 or more - multiple checkboxes can be checked. Checking a checkbox has nothing to do with selecting the row</li>
	 * <li>null - {@link CGridView::selectableRows} is used to control how many checkboxes can be checked.
	 * Cheking a checkbox will also select the row.</li>
	 * </ul>
	 * You may also call the JavaScript function <code>$.fn.yiiGridView.getChecked(containerID,columnID)</code>
	 * to retrieve the key values of the checked rows.
	 * @since 1.1.6
	 */
	public $selectableRows=null;

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

		if($this->selectableRows===null)
		{
			if(isset($this->checkBoxHtmlOptions['class']))
				$this->checkBoxHtmlOptions['class'].=' select-on-check';
			else
				$this->checkBoxHtmlOptions['class']='select-on-check';
			return;
		}

		$cball=$cbcode='';
		if($this->selectableRows==0)
		{
			//.. read only
			$cbcode="return false;";
		}
		elseif($this->selectableRows==1)
		{
			//.. only one can be checked, uncheck all other
			$cbcode="$(\"input:not(#\"+this.id+\")[name='$name']\").prop('checked',false);";
		}
		else
		{
			//.. process check/uncheck all
			$cball=<<<CBALL
$('#{$this->id}_all').live('click',function() {
	var checked=this.checked;
	$("input[name='$name']").each(function() {this.checked=checked;});
});

CBALL;
			$cbcode="$('#{$this->id}_all').prop('checked', $(\"input[name='$name']\").length==$(\"input[name='$name']:checked\").length);";
		}

		$js=$cball;
		$js.=<<<EOD
$("input[name='$name']").live('click', function() {
	$cbcode
});
EOD;
		Yii::app()->getClientScript()->registerScript(__CLASS__.'#'.$this->id,$js);
	}

	/**
	 * Renders the header cell content.
	 * This method will render a checkbox in the header when {@link selectableRows} is greater than 1
	 * or in case {@link selectableRows} is null when {@link CGridView::selectableRows} is greater than 1.
	 */
	protected function renderHeaderCellContent()
	{
		if($this->selectableRows===null && $this->grid->selectableRows>1)
			echo CHtml::checkBox($this->id.'_all',false,array('class'=>'select-on-check-all'));
		else if($this->selectableRows>1)
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
