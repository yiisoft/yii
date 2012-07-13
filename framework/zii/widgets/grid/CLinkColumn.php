<?php
/**
 * CLinkColumn class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

Yii::import('zii.widgets.grid.CGridColumn');

/**
 * CLinkColumn represents a grid view column that renders a hyperlink in each of its data cells.
 *
 * The {@link label} and {@link url} properties determine how each hyperlink will be rendered.
 * The {@link labelExpression}, {@link urlExpression} properties may be used instead if they are available.
 * In addition, if {@link imageUrl} is set, an image link will be rendered.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package zii.widgets.grid
 * @since 1.1
 */
class CLinkColumn extends CGridColumn
{
	/**
	 * @var string the attribute name of the data model. Used for column sorting, filtering and to render the corresponding
	 * attribute value in each link cell. If {@link value} is specified it will be used to rendered the data cell instead of the attribute value.
	 * @see value
	 * @see sortable
	 */
	public $name;
	/**
	 * @var boolean whether the column is sortable. If so, the header cell will contain a link that may trigger the sorting.
	 * Defaults to true. Note that if {@link name} is not set, or if {@link name} is not allowed by {@link CSort},
	 * this property will be treated as false.
	 * @see name
	 */
	public $sortable=true;

	/**
	 * @var string the label to the hyperlinks in the data cells. Note that the label will not
	 * be HTML-encoded when rendering. This property is ignored if {@link labelExpression} or {@link name} is set.
	 * @see labelExpression
	 */
	public $label='Link';
	/**
	 * @var string a PHP expression that will be evaluated for every data cell and whose result will be rendered
	 * as the label of the hyperlink of the data cells. In this expression, the variable
	 * <code>$row</code> the row number (zero-based); <code>$data</code> the data model for the row;
	 * and <code>$this</code> the column object.
	 */
	public $labelExpression;
	/**
	 * @var string the URL to the image. If this is set, an image link will be rendered.
	 */
	public $imageUrl;
	/**
	 * @var string the URL of the hyperlinks in the data cells.
	 * This property is ignored if {@link urlExpression} is set.
	 * @see urlExpression
	 */
	public $url='javascript:void(0)';
	/**
	 * @var string a PHP expression that will be evaluated for every data cell and whose result will be rendered
	 * as the URL of the hyperlink of the data cells. In this expression, the variable
	 * <code>$row</code> the row number (zero-based); <code>$data</code> the data model for the row;
	 * and <code>$this</code> the column object.
	 */
	public $urlExpression;
	/**
	 * @var array the HTML options for the data cell tags.
	 */
	public $htmlOptions=array('class'=>'link-column');
	/**
	 * @var array the HTML options for the header cell tag.
	 */
	public $headerHtmlOptions=array('class'=>'link-column');
	/**
	 * @var array the HTML options for the footer cell tag.
	 */
	public $footerHtmlOptions=array('class'=>'link-column');
	/**
	 * @var array the HTML options for the hyperlinks
	 */
	public $linkHtmlOptions=array();
	/**
	 * @var mixed the HTML code representing a filter input (eg a text field, a dropdown list)
	 * that is used for this data column. This property is effective only when
	 * {@link CGridView::filter} is set.
	 * If this property is not set, a text field will be generated as the filter input;
	 * If this property is an array, a dropdown list will be generated that uses this property value as
	 * the list options.
	 * If you don't want a filter for this link column, set this value to false.
	 * @since 1.1.11
	 */
	public $filter;

	public function init() {
		parent::init();
		if($this->name===null)
			$this->sortable=false;
	}

	/**
	 * Renders the header cell content.
	 * This method will render a link that can trigger the sorting if the column is sortable.
	 */
	protected function renderHeaderCellContent()
	{
		if($this->grid->enableSorting && $this->sortable && $this->name!==null)
			echo $this->grid->dataProvider->getSort()->link($this->name,$this->header,array('class'=>'sort-link'));
		else if($this->name!==null && $this->header===null)
		{
			if($this->grid->dataProvider instanceof CActiveDataProvider)
				echo CHtml::encode($this->grid->dataProvider->model->getAttributeLabel($this->name));
			else
				echo CHtml::encode($this->name);
		}
		else
			parent::renderHeaderCellContent();
	}

	/**
	 * Renders the filter cell content.
	 * This method will render the {@link filter} as is if it is a string.
	 * If {@link filter} is an array, it is assumed to be a list of options, and a dropdown selector will be rendered.
	 * Otherwise if {@link filter} is not false, a text field is rendered.
	 * @since 1.1.11
	 */
	protected function renderFilterCellContent()
	{
		if(is_string($this->filter))
			echo $this->filter;
		else if($this->filter!==false && $this->grid->filter!==null && $this->name!==null && strpos($this->name,'.')===false)
		{
			if(is_array($this->filter))
				echo CHtml::activeDropDownList($this->grid->filter, $this->name, $this->filter, array('id'=>false,'prompt'=>''));
			else if($this->filter===null)
				echo CHtml::activeTextField($this->grid->filter, $this->name, array('id'=>false));
		}
		else
			parent::renderFilterCellContent();
	}

	/**
	 * Renders the data cell content.
	 * This method renders a hyperlink in the data cell.
	 * @param integer $row the row number (zero-based)
	 * @param mixed $data the data associated with the row
	 */
	protected function renderDataCellContent($row,$data)
	{
		if($this->urlExpression!==null)
			$url=$this->evaluateExpression($this->urlExpression,array('data'=>$data,'row'=>$row));
		else
			$url=$this->url;
		if($this->labelExpression!==null)
			$label=$this->evaluateExpression($this->labelExpression,array('data'=>$data,'row'=>$row));
		else if($this->name!==null)
			$label=CHtml::value($data,$this->name);
		else
			$label=$this->label;
		$options=$this->linkHtmlOptions;
		if(is_string($this->imageUrl))
			echo CHtml::link(CHtml::image($this->imageUrl,$label),$url,$options);
		else
			echo CHtml::link($label,$url,$options);
	}
}
