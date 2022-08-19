<?php
/**
 * CGridColumn class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link https://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

/**
 * CGridColumn is the base class for all grid view column classes.
 *
 * A CGridColumn object represents the specification for rendering the cells in
 * a particular grid view column.
 *
 * In a column, there is one header cell, multiple data cells, and an optional footer cell.
 * Child classes may override {@link renderHeaderCellContent}, {@link renderDataCellContent}
 * and {@link renderFooterCellContent} to customize how these cells are rendered.
 *
 * @property boolean $hasFooter Whether this column has a footer cell.
 * This is determined based on whether {@link footer} is set.
 * @property string $filterCellContent The filter cell content.
 * @property string $headerCellContent The header cell content.
 * @property string $footerCellContent The footer cell content.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package zii.widgets.grid
 * @since 1.1
 */
abstract class CGridColumn extends CComponent
{
	/**
	 * @var string the ID of this column. This value should be unique among all grid view columns.
	 * If this is not set, it will be assigned one automatically.
	 */
	public $id;
	/**
	 * @var CGridView the grid view object that owns this column.
	 */
	public $grid;
	/**
	 * @var string the header cell text. Note that it will not be HTML-encoded.
	 */
	public $header;
	/**
	 * @var string the footer cell text. Note that it will not be HTML-encoded.
	 */
	public $footer;
	/**
	 * @var boolean whether this column is visible. Defaults to true.
	 */
	public $visible=true;
	/**
	 * @var string a PHP expression that is evaluated for every data cell and whose result
	 * is used as the CSS class name for the data cell. In this expression, you can use the following variables:
	 * <ul>
	 *   <li><code>$row</code> the row number (zero-based).</li>
	 *   <li><code>$data</code> the value provided by grid view object for the row.</li>
	 *   <li><code>$this</code> the column object.</li>
	 * </ul>
	 * Type of the <code>$data</code> depends on {@link IDataProvider data provider} which is passed to the 
	 * {@link CGridView grid view object}. In case of {@link CActiveDataProvider}, <code>$data</code> will have
	 * object type and its values are accessed like <code>$data->property</code>. In case of 
	 * {@link CArrayDataProvider} or {@link CSqlDataProvider}, it will have array type and its values must be
	 * accessed like <code>$data['property']</code>.
	 * 
	 * The PHP expression will be evaluated using {@link evaluateExpression}.
	 *
	 * A PHP expression can be any PHP code that has a value. To learn more about what an expression is,
	 * please refer to the {@link https://www.php.net/manual/en/language.expressions.php php manual}.
	 */
	public $cssClassExpression;
	/**
	 * @var array the HTML options for the data cell tags.
	 */
	public $htmlOptions=array();
	/**
	 * @var array the HTML options for the filter cell tag.
	 */
	public $filterHtmlOptions=array();
	/**
	 * @var array the HTML options for the header cell tag.
	 */
	public $headerHtmlOptions=array();
	/**
	 * @var array the HTML options for the footer cell tag.
	 */
	public $footerHtmlOptions=array();

	/**
	 * Constructor.
	 * @param CGridView $grid the grid view that owns this column.
	 */
	public function __construct($grid)
	{
		$this->grid=$grid;
	}

	/**
	 * Initializes the column.
	 * This method is invoked by the grid view when it initializes itself before rendering.
	 * You may override this method to prepare the column for rendering.
	 */
	public function init()
	{
	}

	/**
	 * @return boolean whether this column has a footer cell.
	 * This is determined based on whether {@link footer} is set.
	 */
	public function getHasFooter()
	{
		return $this->footer!==null;
	}

	/**
	 * Renders the filter cell.
	 * @since 1.1.1
	 */
	public function renderFilterCell()
	{
		echo CHtml::openTag('td',$this->filterHtmlOptions);
		$this->renderFilterCellContent();
		echo "</td>";
	}

	/**
	 * Renders the header cell.
	 */
	public function renderHeaderCell()
	{
		$this->headerHtmlOptions['id']=$this->id;
		echo CHtml::openTag('th',$this->headerHtmlOptions);
		$this->renderHeaderCellContent();
		echo "</th>";
	}

	/**
	 * Renders a data cell.
	 * @param integer $row the row number (zero-based)
	 */
	public function renderDataCell($row)
	{
		$data=$this->grid->dataProvider->data[$row];
		$options=$this->htmlOptions;
		if($this->cssClassExpression!==null)
		{
			$class=$this->evaluateExpression($this->cssClassExpression,array('row'=>$row,'data'=>$data));
			if(!empty($class))
			{
				if(isset($options['class']))
					$options['class'].=' '.$class;
				else
					$options['class']=$class;
			}
		}
		echo CHtml::openTag('td',$options);
		$this->renderDataCellContent($row,$data);
		echo '</td>';
	}

	/**
	 * Renders the footer cell.
	 */
	public function renderFooterCell()
	{
		echo CHtml::openTag('td',$this->footerHtmlOptions);
		$this->renderFooterCellContent();
		echo '</td>';
	}

	/**
	 * Returns the header cell content.
	 * The default implementation simply returns {@link header}.
	 * This method may be overridden to customize the rendering of the header cell.
	 * @return string the header cell content.
	 * @since 1.1.16
	 */
	public function getHeaderCellContent()
	{
		return $this->header!==null && trim($this->header)!=='' ? $this->header : $this->grid->blankDisplay;
	}

	/**
	 * Renders the header cell content.
	 * @deprecated since 1.1.16. Use {@link getHeaderCellContent()} instead.
	 */
	protected function renderHeaderCellContent()
	{
		echo $this->getHeaderCellContent();
	}

	/**
	 * Returns the footer cell content.
	 * The default implementation simply returns {@link footer}.
	 * This method may be overridden to customize the rendering of the footer cell.
	 * @return string the footer cell content.
	 * @since 1.1.16
	 */
	public function getFooterCellContent()
	{
		return $this->footer!==null && trim($this->footer)!=='' ? $this->footer : $this->grid->blankDisplay;
	}

	/**
	 * Renders the footer cell content.
	 * @deprecated since 1.1.16. Use {@link getFooterCellContent()} instead.
	 */
	protected function renderFooterCellContent()
	{
		echo $this->getFooterCellContent();
	}

	/**
	 * Returns the data cell content.
	 * This method SHOULD be overridden to customize the rendering of the data cell.
	 * @param integer $row the row number (zero-based)
	 * The data for this row is available via <code>$this->grid->dataProvider->data[$row];</code>
	 * @return string the data cell content.
	 * @since 1.1.16
	 */
	public function getDataCellContent($row)
	{
		return $this->grid->blankDisplay;
	}

	/**
	 * Renders the data cell content.
	 * @param integer $row the row number (zero-based)
	 * @param mixed $data the data associated with the row
	 * @deprecated since 1.1.16. Use {@link getDataCellContent()} instead.
	 */
	protected function renderDataCellContent($row,$data)
	{
		echo $this->getDataCellContent($row);
	}

	/**
	 * Returns the filter cell content.
	 * The default implementation simply returns an empty column.
	 * This method may be overridden to customize the rendering of the filter cell (if any).
	 * @return string the filter cell content.
	 * @since 1.1.16
	 */
	public function getFilterCellContent()
	{
		return $this->grid->blankDisplay;
	}

	/**
	 * Renders the filter cell content.
	 * @since 1.1.1
	 * @deprecated since 1.1.16. Use {@link getFilterCellContent()} instead.
	 */
	protected function renderFilterCellContent()
	{
		echo $this->getFilterCellContent();
	}
}
