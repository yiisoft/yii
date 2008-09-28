<?php
/**
 * CContentDecorator class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CContentDecorator decorates the content it encloses with the specified view.
 *
 * CContentDecorator is mostly used to implement nested layouts, i.e., a layout
 * is embedded within another layout. {@link CBaseController} defines a pair of
 * convenient methods to use CContentDecorator:
 * <pre>
 * $this->beginContent('path/to/view');
 * // ... content to be decorated
 * $this->endContent();
 * </pre>
 *
 * The property {@link view} specifies the name of the view that is used to
 * decorate the content. In the view, the content being decorated may be
 * accessed with variable <code>$content</code>.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.web.widgets
 * @since 1.0
 */
class CContentDecorator extends COutputProcessor
{
	/**
	 * @var string the name of the view that will be used to decorate the captured content.
	 */
	public $view;

	/**
	 * Processes the captured output.
     * This method decorates the output with the specified {@link view}.
	 * @param string the captured output to be processed
	 */
	public function processOutput($output)
	{
		$output=$this->decorate($output);
		parent::processOutput($output);
	}

	/**
	 * Decorates the content by rendering a view and embedding the content in it.
	 * The content being embedded can be accessed in the view using variable <code>$content</code>
	 * The decorated content will be displayed directly.
	 * @param string the content to be decorated
	 * @return string the decorated content
	 */
	protected function decorate($content)
	{
		if($this->view===null)
			throw new CException(Yii::t('yii##The "view" property is required.'));
		$owner=$this->getOwner();
		if(($viewFile=$owner->getViewFile($this->view))!==false)
			return $owner->renderFile($viewFile,array('content'=>$content),true);
		else
			throw new CException(Yii::t('yii##Unable to find the decorator view "{view}".',array('{view}'=>$this->view)));
	}
}
